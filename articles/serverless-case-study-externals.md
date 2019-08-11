---
layout: post
title: "From LAMP to serverless: case study of externals.io"
date: 2019-08-11 18:00
comments: true
image: https://mnapoli.fr/images/posts/externals/stack-after.svg
---

*This article is part of a series of case studies of serverless PHP applications built with [Bref](https://bref.sh/) on AWS Lambda. You can [read more of those case studies here](https://bref.sh/docs/case-studies.html).*

This case study is about migrating the [externals.io](https://externals.io/) website to AWS Lambda using [Bref](https://bref.sh/). This is the first time I write about a **serverless PHP website with a MySQL database**. I hope it will interest a few people ;)

<!--more-->

Externals is a read-only interface for PHP's #internals mailing list. This mailing list is where PHP core developers discuss the future of the language.

[![](/images/posts/externals/externals.png)](https://externals.io/)

The website (which is [on GitHub](https://github.com/mnapoli/externals)) has been migrated from a classic cloud hosting to AWS Lambda in July. I have now a little bit of data to show you and draw some lessons.

Note that I will not be introducing what AWS Lambda or Bref are in this article: you can read [this page](https://bref.sh/docs/) to learn more.



## Stack

The application is a traditional LAMP stack (Linux, Apache, MySQL, PHP) with the addition of a cron. The cron fetches the last emails of the mailing list and saves them to the database. The application used to run on Platfom.sh via a plan graciously sponsored by the company:

![](/images/posts/externals/stack-before.svg)

The new architecture is very similar. The main difference is that instead of running on one server with traditional services, the application now runs using multiple AWS services:

![](/images/posts/externals/stack-after.svg)

As you can see, while some AWS services have been added to the mix, nothing major has changed. And if you have read the [Bref](https://bref.sh/) documentation at some point, the schema may look familiar: this architecture is documented in the ["Serverless Websites" documentation on bref.sh](https://bref.sh/docs/websites.html).

To be honest, the main complexity in the stack is related to CloudFront. CloudFront is the AWS CDN, and it helps us serve assets (CSS, JS) from S3. It works well, but setting it up requires around 60 lines of YAML boilerplate. There is definitely room for improvement, and [I have a few ideas in mind](https://github.com/serverless/components).

In the last section of this article, I detail **everything I had to change in the code** for the migration. But before that, let's talk performances and pricing.



## Performances

Here is the HTTP response time from API Gateway:

![](/images/posts/externals/performance-graph.png)

As you can see, **the median response time is 55ms**. Pretty good for a website!

Note: this is the full HTTP response time of API Gateway, not just the Lambda execution time (PHP execution time). PHP's execution time is 15-20ms _less_ than API Gateway's response time, i.e. 35ms on average.

What about **cold starts** and slow pages? Here are more numbers:

![](/images/posts/externals/performance-numbers.png)

- 90% of the requests are below 100ms
- 1% of the requests are over 500ms
- 0.5% of all requests are cold starts

Having 1% of requests response in 500ms to 3s is not ideal. However **it is perfectly acceptable here**. Especially when you consider the full page loading time for users (with assets and JS execution), which is often more than 10 seconds on most sites.



## Costs

Externals was previously hosted on Platform.sh, running on the smallest $50/month plan. Though the plan was offered by Platform.sh to support externals.io, let's keep in mind the official price for a fair comparison.

Here is the monthly cost for the serverless version on AWS:

|      | $/month |
|----------------|------:|
| AWS Lambda     | $0.37 |
| API Gateway    | $0.71 |
| CloudFront     | $0.47 |
| AWS S3 (assets) | $0.12 |
| RDS (database) | $15.70 |
| **Total**      | **$17.37** |

(the free tier has been ignored, I actually pay even less than that)

As we can see, most of the cost comes from the database (which is the smallest available, and doesn't break a sweat). The rest of the website totals to $1.67/month.

The traffic received each month by the website is:

- 5200 visitors (Google Analytics)
- 100,000 HTTP requests going to PHP

And in case you wonder who pays the bill of externals.io now: [null](https://null.tc/).

### What about spikes?

Something I hear very often regarding the serverless billing system is:

> What if there's a huge traffic spike? I'll end up paying a lot of money.

That's a real question. Let's try to give some numbers. I don't have a groundbreaking traffic spike to illustrate, but I do have a little one:

![](/images/posts/externals/spike.png)

In the last few days, there has been a lot of activity on the site: internal developers are talking about major changes to the language. This has been linked over Twitter, Reddit, Facebook and Hacker News and that brought some traffic.

![](/images/posts/externals/spike-cost.png)

This caused a spike that costs me $0.01 on Lambda (went from 1c per day to 2.2c). Roughly speaking, the total extra cost of that spike should be around $0.04.

This example doesn't really answer the question of a DDoS (which can be prevented using CloudFlare or AWS WAF) and large scale traffic spikes. But I hope it gives a sense of the scale.

### How to anticipate costs?

This question is a tough one. It's nice saying that the website costs only $17/month, but it's hard jumping forward and hoping for the best.

Over at [Bref](https://bref.sh/) we have built a [serverless pricing calculator](https://cost-calculator.bref.sh/). It is mostly targeted at APIs and websites, and should help you anticipate the prices a little better.

Now let's play a game and see if it would have guessed the cost correctly for externals.io:

[![](/images/posts/externals/serverless-cost-calculator.png)](https://cost-calculator.bref.sh/)

The calculator doesn't include the database yet, but what we can note:

- AWS Lambda is off by half: this is because the calculator doesn't take into account the cron job, which actually costs about as much as the website (because fetching the emails is slow)
- CloudFront is less expensive in reality (maybe I need to account for automatic assets compression in the calculator, which reduces costs a lot)
- the rest seems on par!

I hope the calculator will be useful for you.




## Migration effort

In this section, I get into the details of everything I had to change. I also link to the corresponding Bref documentation for each point.

- Move all the assets (CSS, JS, fonts) in a `/assets/` subfolder. <small>[ℹ️ *bref docs*](https://bref.sh/docs/websites.html)</small>

With Apache or Nginx, we can configure routing to "call PHP unless a file exists, in which case serve the file". That is useful to serve assets like CSS, JS, fonts… We simply put them in a `public/` directory and make that the web root.
  
CloudFront has no "fallback" mechanism. Requests must be routed to PHP or to S3 (assets) based on a URL prefix. I chose to move the CSS, JS and fonts into a `/assets/` URL prefix so that I had 1 rule to configure instead of 3. What can I say, I'm lazy.
  
```diff
-<link rel="stylesheet" href="/css/main.min.css?v={{ version }}">
+<link rel="stylesheet" href="/assets/css/main.min.css?v={{ version }}">
```
  
That being said, I think getting rid of the "fallback" mechanism is a good thing: that has always been source of security issues, and it forced us to put our `index.php` in a `public/` subdirectory. Now things are a bit more explicit and secure.
  
**Effort: low**

- Remove Platform.sh specific code for configuration and use environment variables. <small>[ℹ️ *bref docs*](https://bref.sh/docs/environment/variables)</small>

Platform.sh populated a special environment variable containing some configuration values. I was able to remove all that and replace the configuration of the application by classic environment variables.

This was actually a win. I will spare you the diff since it's only code removal.

**Effort: low**

- Log to `stderr` instead of a file. <small>[ℹ️ *bref docs*](https://bref.sh/docs/environment/logs)</small>

On AWS Lambda, logs should be sent to `/dev/stderr` to be collected by AWS. This is very simple [thanks to the `bref/logger` PSR-3 logger](https://bref.sh/docs/environment/logs.html) where `$log = new \Bref\Logger\StderrLogger()` can replace Monolog.

I use [PHP-DI](http://php-di.org/), here is what the diff looks like:

```diff
-    LoggerInterface::class => create(Monolog\Logger::class)
-        ->constructor('app', get('logger.handlers')),
-    // more Monolog configuration...
+    LoggerInterface::class => create(Bref\Logger\StderrLogger::class),
```

**Effort: low**

- Move the Twig cache from `var/cache` to `/tmp/cache`. <small>[ℹ️ *bref docs*](https://bref.sh/docs/environment/storage.html#application-cache)</small>

The code is mounted as read-only in Lambda. The `/tmp` directory is the only writable directory, which is where I moved Twig's cache.

```diff
-    'path.cache' => __DIR__ . '/../../var/cache',
+    'path.cache' => '/tmp/cache',
```

**Effort: low**

- Move the `./console sync` command into a Lambda function.

I had the `./console sync` command running as a cron every 15 minutes. I could have ported exactly the same command to AWS Lambda, but I decided to make the command more "in line" with what AWS Lambda is about.

Indeed, we don't need a CLI framework and its abstractions (I mean the Symfony Console) in a cron. After all, I just want to execute a function. So I extracted the content of my Symfony command and put it [in a real Lambda function](https://bref.sh/docs/runtimes/function.html):

```php
<?php
$app = require __DIR__ . '/res/bootstrap.php';

$synchronizer = $app->getContainer()->get(Externals\EmailSynchronizer::class);

lambda(function () use ($synchronizer) {
  $synchronizer->synchronize();
});
```

**Effort: low**

- Generate a version number to bust browser cache.

The CSS is included in the page with a version number:

```html
<link rel="stylesheet" href="/assets/css/main.min.css?v={{ version }}">
```

This forces browsers to clear their cache when a new version of the website is deployed. Platform.sh provided a unique version as an environment variable, but I found no such thing on AWS Lambda.

What I had to do is generate a version number when deploying. I use the timestamp in the deployment script:

```bash
export EXTERNALS_APP_VERSION=$$(date +%s)
serverless deploy
```

Then use that environment variable in the PHP-DI config:

```php
-    'version' => env('PLATFORM_TREE_ID'),
+    'version' => env('EXTERNALS_APP_VERSION'),
```

**Effort: medium**

Finally, I obviously had te rewrite the configuration of the application from the Platform.sh format to `serverless.yml`. This is the part that required more effort. I took advantage of this to create [the documentation to create websites on bref.sh](https://bref.sh/docs/websites.html), so it should definitely be much easier for you now ;)



## Conclusion

Well this was a long article, I won't bore you with more text! If you want to read more of this, you can find [more case studies here](https://bref.sh/docs/case-studies.html).

In the end, I am very happy with the result. If you have any question ask them here or ping me [on Twitter](https://twitter.com/matthieunapoli).
