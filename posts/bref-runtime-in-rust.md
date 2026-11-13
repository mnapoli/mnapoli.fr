---
layout: post
title: "Exploring a Rust runtime for Bref"
date: 2026-11-13 10:32
comments: true
image: https://mnapoli.fr/images/posts/bref-runtime-flow.png
---

[Bref](https://bref.sh) runs PHP applications on AWS Lambda. Its runtime is the code that receives AWS Lambda events and passes them to your application, and it is written in PHP. While working on Bref v4, I wanted to know if rewriting it in Rust would make things faster.

The answer is no, because the PHP code in Bref takes almost no time. This article is based on my notes from that experiment.

<!--more-->

## What Bref does on every request

When an HTTP request reaches a PHP application on AWS Lambda, Bref:

- fetches the event from the AWS Lambda runtime API
- converts the event into a FastCGI request
- sends it to PHP-FPM, which runs the application
- converts the response to JSON and sends it back to AWS Lambda

![](/images/posts/bref-runtime-flow.png)

All of that is PHP code running in a PHP process next to PHP-FPM. With Laravel Octane there is no PHP-FPM: Bref's code calls Laravel directly, in the same process.

That's a lot of PHP between AWS Lambda and the application, so I tried two things:

- a PHP extension written in Rust (with [ext-php-rs](https://crates.io/crates/ext-php-rs)) that replaces the part of Bref that fetches events, decodes them and sends responses
- a single Rust binary that embeds PHP and replaces both Bref's PHP code and PHP-FPM, a bit like [FrankenPHP](https://frankenphp.dev/) does with Go

I tested both on AWS Lambda (ARM, 1024 MB, PHP 8.5) with a "hello world" application and a Laravel application.

## Measuring first

Before writing any Rust, I added timers in Bref to see what takes time. Here is a warm Laravel request with PHP-FPM, 5.9 ms in total:

- 4.65 ms: Laravel
- 0.54 ms: sending the response to AWS Lambda
- 0.36 ms: FastCGI and PHP-FPM overhead
- 0.35 ms: Bref's PHP code, half of which is `json_encode()` on the HTML response

On a "hello world" function (no HTTP), Bref's PHP code takes 0.06 ms per invocation.

So there isn't much to optimize. Even if Rust made Bref's code take zero time, a Laravel request would go from 5.9 ms to 5.6 ms.

It's the same for cold starts: Bref's PHP code takes about 9 ms, while starting PHP-FPM takes 80 ms.

I still built the two prototypes to check.

## The Rust extension

The extension replaces the loop that calls the AWS Lambda runtime API. The HTTP calls are done in Rust instead of using curl in PHP.

Results:

- cold starts: no difference
- warm invocations: about 0.3 ms faster

0.3 ms is a third of a "hello world" invocation, but less than 10% of a Laravel Octane request. And to get that, Bref would need to provide a compiled extension for every PHP version and CPU architecture.

That's not worth it.

## Replacing PHP-FPM

The second prototype is a bigger change. One Rust binary contains PHP itself (linked statically). It calls the AWS Lambda runtime API and runs the PHP application in the same process. There is no PHP-FPM, no FastCGI, and no PHP code at all in the runtime.

This time there is a difference:

| | Cold start | Warm request |
|-|-:|-:|
| Hello world, Bref with PHP-FPM | 222 ms | 1.4 ms |
| Hello world, Rust binary | 123 ms | 0.8 ms |
| Laravel, Bref with PHP-FPM | 1097 ms | 6.8 ms |
| Laravel, Rust binary | 1043 ms | 6.0 ms |

Note: Bref v4 will make Laravel cold starts much shorter, but that is not included in this benchmark.

100 ms less on a "hello world" cold start is nice. But with Laravel that's only 5% on cold starts and 11% on warm requests, because most of the time is spent in Laravel.

Also, this gain is not really because of Rust. It's faster because there is no PHP-FPM anymore: there is one process to start instead of two, and no FastCGI between them. I would get the same numbers by writing it in C.

And it would be a lot of work. PHP-FPM parses requests, handles file uploads and timeouts, and when a request crashes the next one still works. Without PHP-FPM, Bref has to do all of that. The prototype also needed some C code to work with PHP's internals (PHP uses `longjmp` for fatal errors, and that doesn't work well with Rust).

## What about Octane?

With Laravel Octane, Bref keeps Laravel in memory between requests, so there is no PHP-FPM to remove. I tried three variants:

- the Rust extension
- the Rust binary running Bref's Octane handler
- the Rust binary filling `$_GET`, `$_POST` and `$_SERVER` itself, like FrankenPHP does

All three give about the same result: 0.2 to 0.4 ms faster on a request that takes 3 to 4 ms. With the Rust binary, cold starts were also 40 to 80 ms slower. I didn't investigate why.

## Three different CPUs

At some point the Rust extension was 1 ms slower than Bref on Laravel. That made no sense.

It turns out AWS Lambda on ARM (in the Paris region at least) runs functions on three generations of Graviton CPUs. You don't choose which one you get, and it can change every time a new Lambda instance starts. The same Laravel request took:

- 5.0 to 5.6 ms on Graviton2
- 3.7 to 4.2 ms on Graviton3
- 3.1 to 3.3 ms on Graviton4

The extension was running on a Graviton2 and Bref on a Graviton3, so I was comparing the CPUs more than the code.

If you benchmark on AWS Lambda with ARM, log the CPU model (it's in `/proc/cpuinfo`) and compare results on the same CPU. All the numbers in this article are from Graviton3.

## Conclusion

Bref's runtime will stay in PHP in Bref v4.

A few hundred lines of PHP run on every request and they cost less than half a millisecond. PHP is just that fast, I can't complain about it :)

I'm glad I added the timers before anything else, because they already showed what the prototypes confirmed later.

If you want faster cold starts with Laravel, optimize the application instead of the runtime: [caching the config and the opcode cache before deploying](/optimizing-laravel-aws-lambda/) gives much better results.

The one idea I want to keep exploring is running PHP without PHP-FPM. But that's a much bigger project than rewriting the runtime.
