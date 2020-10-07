---
layout: post
title: "Serverless Laravel"
date: 2018-05-25 12:00
comments: true
image: https://mnapoli.fr/images/posts/serverless-laravel.png
tags:
    - serverless
---

**Update: Since November 2018 AWS Lambda supports PHP via *custom runtimes*. Bref has changed to take advantage of that and the Laravel integration has changed accordingly. Read more about it [in Bref's documentation for Laravel](https://bref.sh/docs/frameworks/laravel.html).**

The article below is now obsolete.

---

Last week I introduced [Bref as a solution to running PHP serverless](/serverless-php/).

Today let's try to deploy a Laravel application on AWS lambda using [Bref](https://github.com/mnapoli/bref). The code shown in this article is [available on GitHub](https://github.com/mnapoli/bref-laravel-demo).

**You can check out the demo Laravel application on AWS lambda here: [https://k6ay4xiyld.execute-api.eu-west-3.amazonaws.com/dev](https://k6ay4xiyld.execute-api.eu-west-3.amazonaws.com/dev).** It is a simple application that uses a 3rd party HTTP API to convert between currencies.

<!--more-->

## An introduction to serverless PHP

Serverless basically means "Running apps without worrying about servers". The main difference with a traditional hosting is that you do not maintain the servers and reserve their capacity. They are scaled up or down automatically and you pay only for what you use.

One notable example is AWS S3: instead of renting a fix disk space, you pay for only the storage you are actually using. AWS S3 has no limits, you can store 10kb or 10Tb and you do not need to scale up or down the storage. **AWS S3 is serverless file storage.**

The idea behind serverless applications, aka Function as a Service (FaaS) is to apply the same principles:

- traditional hosting:
    - pay for fixed server resources (CPU, RAM…)
    - if you hit the limit you need to scale up the server
    - you pay for the whole server even if your application has no traffic
- serverless hosting:
    - no fixed server resources
    - the application is run whenever there is traffic
    - there are as many processes running as there are concurrent requests
    - you pay only for the execution time, an application with low traffic will have costs close to $0

Serverless hosting has the advantages of scaling very well since there are (theoretically) no limits. It can also help optimize costs by avoiding paying for unused server resources. You can read more about [advantages and drawbacks here](/serverless-php/#advantages).

### Making PHP work on AWS Lambda

I will take the example of AWS Lambda because it is the most popular provider for serverless applications, but there are [other providers available](https://serverless.com/framework/docs/). Unfortunately AWS Lambda does not support PHP (supported languages are for example Javascript, Go, Python…). To run PHP we must add the PHP binary in the lambda and have, for example, Javascript execute it.

The technical details can be found [in this section](/serverless-php/#making-php-work-on-aws-lambda) so I will not cover them again. The conclusion is that deploying PHP on serverless providers is possible but a PITA. That is how Bref is born.

Bref's goals are:

- deploy easily on serverless providers
- make PHP frameworks work just like before

For the first part Bref builds on top of [the serverless framework](https://serverless.com/) and brings additional tooling specific to PHP.

For the second part I have been working on bridges to make PHP frameworks work on lambdas.

## Laravel on lambdas

**What follows is a step-by-step explanation, if you want the short version either check out [the Bref documentation](https://github.com/mnapoli/bref/blob/master/docs/Laravel.md) or [the demo on GitHub](https://github.com/mnapoli/bref-laravel-demo).** I will also focus on AWS Lambda here because it is what I used for those tests.

Let's start by creating a new Laravel application:

```shell
$ composer create-project laravel/laravel demo
$ cd demo
```

Now let's install Bref (read the [setup instructions](https://github.com/mnapoli/bref#setup)) and initialize it:

```shell
$ composer require mnapoli/bref
$ vendor/bin/bref init
```

When applications run on lambdas they do not work like with traditional hosting providers: `public/index.php` is no longer the application's entry point because there is no Apache, Nginx or PHP-FPM. Bref takes care of connecting the HTTP layer (API Gateway + AWS lambda integration) to your application. It will convert the HTTP request from API Gateway into a format Laravel can understand, and will convert back the HTTP response the other way around.

![](/images/posts/serverless-laravel.png)

*Note: you don't need to know about API Gateway, Bref takes care of that part.*

All we need to do is write a `bref.php` file: that will be the entrypoint of the application. Let's write that file at the root of the project:

```php
<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$app = new \Bref\Application;
$app->httpHandler(new Bref\Bridge\Laravel\LaravelAdapter($kernel));
$app->run();
```

As you can see it's very similar to `public/index.php` except that Laravel will not run itself, instead Bref will run Laravel.

That is done using the `$app->httpHandler(...)` line ([documentation](https://github.com/mnapoli/bref#http-applications)). You can use any PSR-17 framework as a HTTP handler, here we are configuring the Laravel HTTP Kernel using an adapter. The adapter is necessary because Laravel is not compliant with PSR-17 yet.

The next step is to configure which directories to deploy on the lambda. That can be configured in the `serverless.yml` file that was created in your project:

```yaml
package:
  exclude:
    # ...
  include:
    # ...
    # Add the following directories:
    - 'app/**'
    - 'bootstrap/**'
    - 'config/**'
    - 'resources/**'
    - 'routes/**'
    - 'vendor/**'
```

Now all we would have to do in theory would be to deploy using:

```shell
$ vendor/bin/bref deploy
```

But with Laravel we have a few extra steps to take.

## File storage

The filesystem on lambdas is read-only, except for the `/tmp` folder. That means that Laravel's `storage` directory will not work out of the box if we keep it inside our project.

We need to tell Laravel to use the `/tmp/storage` folder instead. Add this line in `bootstrap/app.php` after `$app = new Illuminate\Foundation\Application`:

```php
/*
 * Allow overriding the storage path in production using an environment variable.
 */
$app->useStoragePath(env('APP_STORAGE', $app->storagePath()));
```

By using an environment variable we can keep the classic behavior when running the application locally (or on a traditional server) and we can set this variable to `/tmp/storage` on AWS lambda.

To set environment variables on AWS lambda we can use `serverless.yml`:

```yaml
functions:
  main:
    ...
    environment:
      APP_STORAGE: '/tmp/storage'
```

Now since this directory doesn't exist by default on AWS lambda we need to create it on the fly. Add these lines to `bref.php`:


```php
// ...

$app = require_once __DIR__.'/bootstrap/app.php';

// Laravel does not create that directory automatically so we have to create it
if (!is_dir(storage_path('framework/views'))) {
    if (!mkdir(storage_path('framework/views'), 0755, true)) {
        die('Cannot create directory ' . storage_path('framework/views'));
    }
}

// ...
```

## Configuration

There are other parameters we will want to override for the production environment. What we will do is create a `.env.production` file in our project and configure our variables here:

```dotenv
APP_ENV=production
APP_DEBUG=false
# ...
```

To make Laravel use this configuration in production we will write a *build hook*. Build hooks are scripts that are executed **before** Bref deploys your application. Let's write those in a `.bref.yml` file:

```yaml
hooks:
    build:
        # Rename the `.env.production` file to `.env`
        - 'rm .env && cp .env.production .env'
```

*Note: those commands are run in a separate directory than your project, your own `.env` file will **not** be deleted.*

By default Bref installs Composer dependencies and optimizes the autoloader, we do not need to do it ourselves.

In order to optimize the application we want to [cache the configuration](https://laravel.com/docs/5.6/configuration#configuration-caching). Let's add other hooks to the list:

```yaml
hooks:
    build:
        # Use the `.env.production` file as `.env`
        - 'rm .env && cp .env.production .env'
        - 'rm bootstrap/cache/*.php'
        - 'php artisan config:cache'
```

The `php artisan config:cache` dumps the optimized configuration files. The problem with that is that it will also dump all file paths as absolute paths (e.g. storage directory, views, etc.). Since Laravel is in a different directory on our computer than on AWS lambda the dumped configuration will not work in production.

We need to tell Laravel to generate **relative paths**. Let's do that by customizing the root path of Laravel in `bootstrap/app.php` as shown below. The `APP_DIR` variable will allow us to replace the absolute path by `.` (relative path) when generating the lambda.

```php
$app = new Illuminate\Foundation\Application(
    env('APP_DIR', realpath(__DIR__.'/../'))
);
```

*Note: we do not want to hardcode `.` directly here because that would mess up Laravel's behavior in other cases (for example with `artisan serve` or when running Laravel with Apache/Nginx).*

Let's add that new environment variable to `serverless.yml`:

```yaml
functions:
  main:
    ...
    # Laravel configuration using environment variables:
    environment:
      APP_STORAGE: '/tmp/storage'
      APP_DIR: '.'
```

Let's also add that variable into our `.env.production` file:

```dotenv
# ...

# This allows to generate relative file paths for the lambda
APP_DIR=.
APP_STORAGE=/tmp/storage
```

Everything is good except one last problem: view cache files. Laravel runs `realpath()` on the path containing the cached views, and we are generating a configuration with `/tmp/storage` on our machine -> that directory doesn't exist on our machine, and `realpath()` fails. That breaks Laravel's views.

We need to remove the `realpath()` call in `config/views.php`:

```diff
-    'compiled' => realpath(storage_path('framework/views')),
+    'compiled' => storage_path('framework/views'),
```

All is good now!

## Logging

We configured Laravel to use the `/tmp/storage` directory, but `/tmp` is ephemeral: everything in there will be lost at some point. This is not a good place to store logs.

We need to change the logging driver and instead use the `stderr` driver: logs will be captured by Bref and sent directly to [AWS Cloudwatch](https://aws.amazon.com/cloudwatch/). That can be configured in `.env.production`:

```dotenv
# ...

LOG_CHANNEL=stderr
```

You can of course use [any other driver you want](https://laravel.com/docs/5.6/logging) (as long as it's not the `file` driver).

## Sessions

Just like logging, storing sessions in `/tmp/storage` is not a good idea. If you are writing an API or any application that do not need sessions you can use the `array` driver:

```dotenv
# ...

SESSION_DRIVER=array
```

If you need sessions you can store them [in database, Redis, etc](https://laravel.com/docs/5.6/session).

## Routing

Finally when deploying a lambda AWS creates a random URL that contains [a `/dev` suffix](https://github.com/mnapoli/bref#why-is-there-a-dev-prefix-in-the-urls-on-aws-lambda). Because of that the default route (`/`) will not work out of the box. We can change the route for the welcome page:

```php
// routes/web.php

Route::get('/dev', function () {
    return view('welcome');
});
```

If you use a custom domain however the suffix will disappear so it is only an issue when doing small tests like this.

## Deploying

We are finally done for a functional serverless Laravel. Let's deploy:

```shell
$ vendor/bin/bref deploy
```

Our app should now be online, get the URL of the app by running `vendor/bin/bref info`.

## Going further

There are still many things to improve with Bref and Laravel:

- simplify the setup and configuration
- build the views before deploying (this is very hard because Laravel uses absolute paths again here, I hope to submit a solution for that soon)
- integrate `artisan` to be able to run on lambdas
- integrate queues and scheduling to run them on lambdas
- integrate with CDN for hosting assets
- etc.

I am planning to work on those topics in the months to come, and help is always welcome.

## Conclusion

If you want to learn more about Bref and Laravel:

- read the [Bref documentation](https://github.com/mnapoli/bref)
- here is [the serverless Laravel demo](https://k6ay4xiyld.execute-api.eu-west-3.amazonaws.com/dev)
- check out [the code for the Laravel demo](https://github.com/mnapoli/bref-laravel-demo)

To learn more about serverless in general:

- check out this [article about running PHP serverless](/serverless-php/)
- and this [article about performances](/serverless-php-performances/)

If you want to get started I can also accompany you, drop me a line at matthieu at mnapoli dot fr.
