---
layout: post
title: "Optimizing Laravel cold starts on AWS Lambda"
date: 2025-09-08 12:32
comments: true
---

This article is based on my notes after experimenting with caching as much as possible before deploying a Laravel application to AWS Lambda using [Bref](https://bref.sh). The goal was to optimize AWS Lambda cold starts, without slowing down warm invocations.

<!--more-->

## Caching Laravel configuration, routes, and more

Bref provides built-in support for Laravel via a package. By default, this integration will cache the Laravel configuration on startup, i.e. on AWS Lambda cold start. That allows making subsequent requests faster.

This is the most basic step to optimizing Laravel. Laravel can "cache" many things (config, routes, etc.), and these are often done on deployment before traffic reaches the application.

We can replicate that with Bref, but the main thing to keep in mind is to do these caching operations in an environment as close to production as possible. Indeed, Laravel will sometimes **hardcode absolute paths** or even environment variables (if you cache the config) in the cached configuration.

To solve that, we need to run caching commands in Docker, using the Bref-provided container images.

One solution is to run Laravel commands like `route:cache` in a Bref container by mounting the application in `/var/task` (the path where the app runs in Lambda):

```shell
docker run --rm -it --entrypoint=php -v $(pwd):/var/task bref/php-84:2 artisan route:cache
```

Another approach would be to deploy the application as a container image instead of a zip file. In that case, we can build the image entirely the way we want to. Here’s an example:

```dockerfile
# syntax=docker/dockerfile:1.7
FROM bref/php-84:2

# Copy the application code
COPY --link . /var/task

# Clear any cached config that might reference local paths
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

RUN php artisan optimize

# The views are compiled in /tmp/storage/framework/views, let's copy them to `storage/framework/views` so that they are persisted
RUN mkdir -p storage/framework/views \
    && cp -r /tmp/storage/framework/views/* storage/framework/views/ \
    && rm -rf /tmp/*

# Make sure PHP on Lambda will be able to read these files
RUN chmod -R 777 storage/framework/views
RUN chmod -R 777 bootstrap/cache
```

One important thing to note if you want to compile the configuration before deploying: all environment variables will be hardcoded in the cached configuration.

That means you must have **production** environment variables set during that build. One way is to create the `.env` file with production secrets and copy that with the application in the container image. Then, `php artisan config:cache` (or `optimize`) will be able to cache those variables. If you do this, keep in mind that the container image contains production secrets, treat it appropriately (for example by tightening permissions to ECR).

## Pre-warming the opcode cache

Another option I explored was pre-compiling PHP's opcode cache before deploying. That would enable PHP-FPM, on startup, to start with a partially warm opcache (avoiding reading PHP files from disk, parsing them, and compiling them on the fly).

Usually, PHP's opcode cache is stored in memory. But it can also be stored to disk during deployment and loaded from these files on startup in Lambda.

This approach is trickier for several reasons:

1. PHP's opcode cache is 100% tied to the exact PHP version, extensions, config, etc. and not portable between systems
2. deploying PHP's opcode cache makes the deployed archive bigger, which slows down cold starts
3. if the opcode cache is deployed with the app as files, these are mounted as read-only in Lambda and PHP does not like that
4. you need a way to trigger opcache compilation to disk

Let's go through these challenges:

> 1\. PHP's opcode cache is 100% tied to the exact PHP version, extensions, config, etc. and not portable between systems

That can be solved by using the same container image during deployment as during production, which we already did in the previous section. So this is solved if you involve Docker.

> 2\. deploying PHP's opcode cache makes the deployed archive bigger, which slows down cold starts

That is definitely true if you deploy with zip files, which is the default with Bref. Don't try this with zip deployments because the extra MB of opcode caches will cancel all your efforts.

However, when deploying with container images, AWS Lambda performs optimizations (there's a [white paper](https://arxiv.org/abs/2305.13162) about it) and essentially streams the filesystem to Lambda as files are read. In other words, it will only load the files you actually read. This means we can add more files to the image and cold starts will not necessarily slow down.

In my experience, it was a balance between how much you compile and the resulting cold start times. For example, I tried compiling every single PHP file, but this ended up making cold starts worse. This was counter-intuitive: I expected PHP to read the `.bin` file instead of the `.php` one, so things could only be faster.

But when I compiled every PHP file and removed the original `.php` files from the container image, PHP crashed completely. For some reason it needed the original PHP files, and I believe that’s why cold starts would not improve 100% of the time. I know there are options to make opcache _not_ read PHP files, and I tried to use all of them. But PHP always needed the original files no matter what. It might be something I missed, or it might be that "opcache from files" behaves differently than "opcache from memory". Feel free to continue my experiments and prove me wrong, I’d love that!

In the end, I found a good balance by compiling only the files in these directories (this was a Laravel app):

```
bootstrap
app
storage/framework/views
vendor/composer
vendor/bref
vendor/symfony/http-foundation
vendor/symfony/http-kernel
vendor/psr
```

You can use this as a starting point. I aimed at compiling the files that I knew would be used for sure for any HTTP request.

> 3\. if the opcode cache is deployed with the app as files, these are mounted as read-only in Lambda and PHP does not like that

I explored one approach:

- set PHP's opcache directory (on disk) to `/tmp/opcache`, since `/tmp` is the only writable path in Lambda
- during deployment, compile PHP's opcache to e.g. `/bref/opcache`
- on cold start, copy `/bref/opcache` to `/tmp/opcache`

To clarify if you're not familiar with Lambda: `/tmp` will always be empty on cold starts (you can't add files there via your container image), and is not shared between Lambda invocations.

This approach wasn't working well. Copying megabytes of data on startup was slower than deploying without a pre-warmed opcache. PHP is just that fast, and it's hard to complain about it :p

I then tried something else: if PHP could read the opcache as read-only from `/bref/opcache` directly that would be great. After all, we don't need PHP to _write_ to that directory after startup since it will all be kept in memory. But PHP doesn't support that: if a directory is configured for opcache, PHP expects to use it.

But it turns out I wasn't the only one wanting that: [@iamacarpet](https://github.com/iamacarpet) opened an issue in the PHP repository and advocated that exact use case, not specifically for Lambda but also for all container-based deployments where the filesystem is read-only.

He ended up working on [a pull request that was accepted and merged](https://github.com/php/php-src/pull/16551) in… PHP 8.5!

I upgraded my test project to PHP 8.5 (in beta at the time of writing) and Bref v3 (in alpha at the time of writing), and used the new `opcache.file_cache_read_only=1` option. It worked!

> 4\. you need a way to trigger opcache compilation to disk

The last step is to actually compile some PHP files to opcache files during deployment. Here is an example script:

```php
<?php

$dirs = ['bootstrap', 'app', 'storage/framework/views', 'vendor/composer', 'vendor/bref', 'vendor/symfony/http-foundation', 'vendor/symfony/http-kernel', 'vendor/psr'];

foreach ($dirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->getExtension() === 'php') {
            $filename = $f->getPathname();
            $result = opcache_compile_file($filename);
            if ($result === false) {
                echo "[opcache] Failed to compile " . $filename . "\n";
                exit(1);
            }
            // Check the file is compiled
            if (!opcache_is_script_cached($filename)) {
                echo "[opcache] File not cached " . $filename . "\n";
            }
        }
    }
}
```

This is a rough script (yes, it’s ugly), feel free to make it more robust and nicer.

One thing I noticed is that some files would **not** compile, but no error was thrown. Hence the second check with `if (!opcache_is_script_cached($filename))`. I could not explain why some files would not be compiled, and it even seemed a bit random. Anyway, I didn't need _exactly_ all files to be compiled so I ignored this for my tests and moved on.

Putting all this together, here is a Dockerfile:

```dockerfile
# syntax=docker/dockerfile:1.7
FROM bref/php-84:3

COPY --link . /var/task

RUN mkdir -p /bref/opcache

# Clear any cached config that might reference local paths
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

RUN php artisan optimize

# The views are compiled in /tmp/storage/framework/views, let's copy them to `storage/framework/views` so that they are persisted
RUN mkdir -p storage/framework/views \
    && cp -r /tmp/storage/framework/views/* storage/framework/views/ \
    && rm -rf /tmp/*

# `opcache.file_cache_read_only=0` so that opcache actually writes the opcache files
RUN php -d opcache.file_cache_read_only=0 compile-opcache.php

RUN chmod -R 777 storage/framework/views
RUN chmod -R 777 bootstrap/cache
RUN chmod -R 755 /bref/opcache
```

And here is the `php.ini` file I used (both locally and in Lambda):

```ini
opcache.file_cache=/bref/opcache
opcache.file_cache_read_only=1
opcache.file_cache_only=0
opcache.use_cwd=0
opcache.validate_timestamps=0
opcache.file_cache_consistency_checks=0
```

## Results

All this for what? **Cold starts were 40% faster**. This is compared to zip deployments without any cache pre-built.

I would have loved an even more drastic improvement, but it is still significant. Overall, the improvement can be attributed to:

- switching from zip to container deployments (which has better cold starts in itself)
- compiling Laravel's config, routes, etc. before deploying
- compiling opcache before deploying

But Lambda cold starts still need to mount the application and start PHP-FPM. Unless AWS Lambda SnapStart becomes available for custom runtimes, we shouldn’t expect drastic improvements.

I enjoyed digging _very_ deep into this problem, especially as I'm working intensely on Bref v3 and benchmarking so many things to improve performance overall. But I want to reframe the problem: I don't think it's actually one in most situations.

**Cold starts are very rare. About 0.3% of all invocations.**

We tend to see them as developers because when we deploy we are the first to invoke the app after deployment. Most end users don't notice them, especially compared to things like 200ms network latency when visiting a site hosted on the other side of the planet. So I think it's important to put things into perspective and not conflate "working on fun deep technical challenges" with "this must be a very important problem".

And if you do want to play with these ideas and try benchmarking on your own, a few notes for you:

- be very careful how you measure cold starts, it's easy to mess things up (see below)
- never measure averages in metrics (especially those with high variability where random extremes skew averages), use percentiles
- learn how containers start up on Lambda: AWS does a lot of optimizations on the image on the very first invocation. You may see latencies of 5 or even 8 seconds, these will mess up your metrics for no reason (end users would never see this cold start in reality). Instead "warm" the regional AWS Lambda caches for container images.
- don't measure one or two cold starts, you need at least 20 for significant results

As a bonus, here's a CloudWatch Logs Insight query you might want to use:

```
filter @type = "REPORT"
| stats
count(@type) as count,
min(@billedDuration) as min,
avg(@billedDuration) as avg,
pct(@billedDuration, 50) as p50,
max(@billedDuration) as max
by @log, (@initDuration > 0) as coldstart
| sort @log
```
