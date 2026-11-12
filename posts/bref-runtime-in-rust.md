---
layout: post
title: "Exploring a Rust runtime for Bref"
date: 2026-11-13 10:32
comments: true
---

[Bref](https://bref.sh) runs PHP applications on AWS Lambda. Its runtime, the code that receives AWS Lambda events and passes them to your application, is written in PHP. While working on Bref v4, I wanted to know if rewriting it in Rust would make things faster.

Short answer: no. **The PHP code in Bref takes almost no time.** Here are my notes.

<!--more-->

## What Bref does on every request

When an HTTP request reaches a PHP application on AWS Lambda, Bref:

- fetches the event from the AWS Lambda runtime API
- turns the event into a FastCGI request
- sends it to PHP-FPM, which runs the application
- turns the response into JSON and sends it back to AWS Lambda

All of that is PHP code running in a PHP process next to PHP-FPM. With Laravel Octane there is no PHP-FPM: Bref's code calls Laravel directly, in the same process.

That's a lot of PHP between AWS Lambda and the application. So I tried two things:

- a PHP extension written in Rust (with [ext-php-rs](https://crates.io/crates/ext-php-rs)) that replaces Bref's loop: fetching events, decoding them, sending responses
- going all the way: a single Rust binary that embeds PHP and replaces Bref's PHP code *and* PHP-FPM, a bit like [FrankenPHP](https://frankenphp.dev/) does with Go

Both prototypes ran on AWS Lambda (ARM, 1024 MB, PHP 8.5) with a "hello world" application and a Laravel application.

## Measuring first

Before writing any Rust, I added timers in Bref to see where the time goes. Here is a warm Laravel request with PHP-FPM, 5.9 ms in total:

- 4.65 ms: Laravel
- 0.54 ms: sending the response to AWS Lambda
- 0.36 ms: FastCGI and PHP-FPM overhead
- 0.35 ms: Bref's PHP code, half of which is `json_encode()` on the HTML response

On a "hello world" function (no HTTP), Bref's PHP code takes 0.06 ms per invocation.

That already answers most of the question. There isn't much to optimize: even if Rust made Bref's code infinitely fast, a Laravel request would go from 5.9 ms to 5.6 ms.

Cold starts tell the same story. Bref's PHP code takes about 9 ms of a cold start. Starting PHP-FPM takes 80 ms.

## The Rust extension

The extension replaces the loop that talks to AWS Lambda. No more curl, no more JSON handling in PHP, the HTTP calls happen in Rust.

Results:

- cold starts: no difference
- warm invocations: about 0.3 ms faster

0.3 ms is a third of a "hello world" invocation, and less than 10% of a Laravel Octane request. In exchange for that, Bref would need to ship a compiled extension for every PHP version and CPU architecture.

Not worth it.

## Replacing PHP-FPM

The second prototype is more radical. One Rust binary contains PHP itself (linked statically). It talks to AWS Lambda, then runs the PHP application in the same process. No PHP-FPM, no FastCGI, no second process, and no PHP code at all in the runtime.

This one does make a difference:

| | Cold start | Warm request |
|-|-:|-:|
| Hello world, Bref with PHP-FPM | 222 ms | 1.4 ms |
| Hello world, Rust binary | 123 ms | 0.8 ms |
| Laravel, Bref with PHP-FPM | 1097 ms | 6.8 ms |
| Laravel, Rust binary | 1043 ms | 6.0 ms |

100 ms less on a "hello world" cold start is nice. But on Laravel that's 5% on cold starts and 11% on warm requests. Real applications spend their time in the application.

And **the gain doesn't come from Rust**. It comes from removing PHP-FPM: one process to start instead of two, and no FastCGI between them. The same thing written in C would give the same numbers.

The cost is high. PHP-FPM handles a lot that we take for granted: parsing requests, file uploads, timeouts, a crashed request that doesn't take the next one down. Without it, all of that becomes Bref's job. The prototype needed C code anyway to deal with PHP's internals (fatal errors in PHP use `longjmp`, which must never cross Rust code).

## What about Octane?

With Laravel Octane, Bref keeps Laravel in memory between requests, so there is no PHP-FPM to remove. I tried three variants: the Rust extension, the Rust binary running Bref's Octane handler, and the Rust binary filling `$_GET`, `$_POST`, `$_SERVER` itself like FrankenPHP does.

All three land in the same place: 0.2 to 0.4 ms faster on a request that takes 3 to 4 ms. The two variants based on the Rust binary had slower cold starts (40 to 80 ms), I didn't look into why.

## A surprise: three different CPUs

At some point the Rust extension was 1 ms *slower* than Bref on Laravel. That made no sense.

It turns out AWS Lambda on ARM (in the Paris region at least) runs functions on three generations of Graviton CPUs. Which one you get is random, and it changes every time a new Lambda instance starts. The same Laravel request took:

- 5.0 to 5.6 ms on Graviton2
- 3.7 to 4.2 ms on Graviton3
- 3.1 to 3.3 ms on Graviton4

The extension had landed on a Graviton2 and Bref on a Graviton3. I was comparing CPUs, not code.

If you benchmark on AWS Lambda with ARM, log the CPU model (it's in `/proc/cpuinfo`) and compare results on the same CPU. All the numbers in this article are from Graviton3.

## Conclusion

Bref's runtime is PHP code, and it's fast enough that rewriting it in Rust gains nothing worth the trouble. It will stay in PHP in Bref v4.

What I take from this:

- PHP is fast. A few hundred lines of PHP running on every request cost less than half a millisecond.
- measure before rewriting. The timers predicted the results of both prototypes before I wrote any Rust.
- the time is in the application. For Laravel cold starts, [caching the config and the opcode cache before deploying](/optimizing-laravel-aws-lambda/) matters ten times more than anything in the runtime.

The only real lead is running PHP without PHP-FPM. That is a much bigger change than a rewrite, and Rust has little to do with it.
