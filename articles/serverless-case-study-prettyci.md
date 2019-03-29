---
layout: post
title: "Serverless case study: PrettyCI and Laravel Queues"
date: 2019-03-21 18:00
comments: true
---

*This article is part of a series of case studies of serverless PHP applications built with [Bref](https://bref.sh/) on AWS Lambda. If you are not familiar with serverless and Bref, I invite you to read [**Serverless and PHP: introducing Bref**](/serverless-php/).*

This case study is about [prettyci.com](https://prettyci.com/), a SaaS that provides **continuous integration for PHP coding standards** for GitHub repositories.

[![](/images/posts/prettyci-intro.png)](https://prettyci.com/)

The idea is that anytime you push a commit to your GitHub repository, PrettyCI will analyze that commit using [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) or [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer). Since PrettyCI integrates in [GitHub's checks tab](https://blog.github.com/2018-05-07-introducing-checks-api/) you can see the build result directly in your repository without having to leave your work.

<!--more-->

## Architecture

I originally created the project using [Laravel Spark](https://spark.laravel.com/). This is a Laravel project pre-built for creating SaaS applications (and it is pretty awesome by the way).

While Spark takes care of the website, I had to create:

- an API endpoint to receive GitHub webhooks whenever someone pushes new commits
- queue wokers that would analyse each commit with `phpcs` or `php-cs-fixer`

Fortunately Laravel has [a queue system](https://laravel.com/docs/5.7/queues) that is very easy to setup.

[![](/images/posts/prettyci-architecture.png)](/images/posts/prettyci-architecture.png)

After deploying the whole thing to a small [DigitalOcean server](https://m.do.co/c/1f59f177416b) (❤️) using [Laravel Forge](https://forge.laravel.com) (❤️) it was running just fine.

## Serverless workers

Since I was running 4 workers on my server that meant that the system could process at most 4 commits at a time. When the 4 workers were busy, new commits to GitHub would be pending and waiting for a worker to free up.

This is not great for user experience, and while scaling up is doable (see [this great article by Oh Dear](https://ohdear.app/blog/how-to-size-scale-your-laravel-queues) for example) it is a bit more work.

Indeed, that meant adding more servers, which meant more costs and a much higher maintenance effort. This is were my lazy side kicked in.

As I was [working with Lambda for returntrue.win](https://mnapoli.fr/serverless-case-study-returntrue/) at the time, **I decided to migrate the workers to AWS Lambda using [Bref](https://bref.sh/).**

I did the migration in July 2018 and it has been a complete success in my eyes, running great since then.

### Challenges

The migration itself presented some challenges:

- [the filesystem is read-only on Lambda](https://bref.sh/docs/environment/storage.html) except for `/tmp` so I had to change the code a bit to use this directory (to checkout repositories and run the analyses)
- while Lambda is a Linux environment, `git` is not available: I had to compile and upload the `git` binary in my lambda to be able to use it
  - Since then it is now possible to include a "git layer" like [this one](https://github.com/lambci/git-lambda-layer), which makes the whole thing much easier

### Downsides

Now that the system is running, I don't see many downsides:

- the initial setup was a bit more effort than using the Laravel Queue system out of the box
- some problems are a bit harder to debug as it's impossible to SSH into a Lambda and run some tests (you have to go through the cycle of deploying + executing the lambda, or else run the tests locally)
- the `/tmp` folder [is limited to 512MB](https://docs.aws.amazon.com/lambda/latest/dg/limits.html), which made PrettyCI incompatible with projects that have a *huge* repository

### Advantages

- **jobs are never queued anymore**: this is the killer thing for me here: as soon as a commit is pushed to GitHub a new environment will be launched to process it.
  - I often see "Pending build" when using Travis/Circle CI/Gitlab CI because the build is waiting for a free container. With PrettyCI that never happens, which makes the user experience awesome (most pull request statuses are updated under 5 seconds).
- infinite scaling with 0 action from my part
- [pay per use](https://aws.amazon.com/lambda/pricing/), which is actually cheaper than the cheapest DigitalOcean VPS (I pay only the execution time of the workers)
- builds are isolated without me having to deal with containers or virtual machines
- high availability as all the execution environment is managed by AWS. I just have to focus on the code.

[![](/images/posts/prettyci-builds.png)](https://prettyci.com/)

## Conclusion

As explained in [the Bref Maturity Matrix](https://bref.sh/docs/#maturity-matrix), running workers and jobs is an excellent use case for AWS Lambda, and this works perfectly for PrettyCI.

The migration is a bit more effort than "simply" using Laravel Queues or other similar libraries, but this is something we are trying to simplify at [Bref](https://bref.sh/).

If you are interested to learn more about AWS Lambda, you can [check out other articles](/) and subscribe to the [serverless PHP newsletter](https://serverless-php.news/).
