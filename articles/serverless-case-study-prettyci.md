---
layout: post
title: "Serverless case study: PrettyCI and Laravel Queues"
date: 2018-10-29 18:00
comments: true
---

*This article is part of a series of case studies of serverless PHP applications built with [Bref](https://github.com/mnapoli/bref) on AWS Lambda. If you are not familiar with serverless and Bref, I invite you to read [**Serverless and PHP: introducing Bref**](/serverless-php/).*

This case study is about [prettyci.com](https://prettyci.com/), a SaaS that provides **continuous integration for PHP coding standards on GitHub**.

[![](/images/posts/prettyci-intro.png)](https://prettyci.com/)

The idea is that anytime you push a commit to your GitHub repository, PrettyCI will analyze that commit using [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) or [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer). Since PrettyCI integrates in [GitHub's checks tab](https://blog.github.com/2018-05-07-introducing-checks-api/) you can see the build result directly in your repository without having to leave your work.

<!--more-->

## Architecture

I originally created the project using [Laravel Spark](https://spark.laravel.com/). This is a Laravel project that comes with a pre-built website ready for creating SaaS applications. By the way I highly recommend Spark if you are looking into starting a SaaS.

While Spark takes care of the website, I had to create:

- an API endpoint to receive GitHub webhooks whenever someone pushes new commits
- queue wokers that would process each webhook asynchronously

Fortunately Laravel has [a queue system](https://laravel.com/docs/5.7/queues) that is very easy to setup. After deploying the whole thing to a [DigitalOcean](https://m.do.co/c/1f59f177416b) server using [Laravel Forge](https://forge.laravel.com) it was running just fine.

[![](/images/posts/prettyci-architecture.png)](/images/posts/prettyci-architecture.png)

## Serverless workers

Since I was running 5 workers on my server that meant that the system could process at most 5 commits at a time. When the 5 workers were busy new commits to GitHub would be pending and waiting for a worker to free up.

This is not good user experience.

What I would have to do is add more workers by scaling up.

That meant I had two options:

- add more workers by scaling up, but that would be costly and would require an action from me any

Scaling queues and workers is doable (see [this great article by Oh Dear](https://ohdear.app/blog/how-to-size-scale-your-laravel-queues) for example). But that meant:
