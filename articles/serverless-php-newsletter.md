---
layout: post
title: "Launching the Serverless PHP newsletter"
date: 2019-01-07 12:00
isPopular: false
comments: true
tags:
    - serverless
---

Serverless is a topic that is getting more and more attention lately. While this is exciting, it also means there is a lot to follow, read and test.

I have met several people who are interested about serverless and its possibilities but have a lot of trouble following everything. This is even more true in the PHP ecosystem because PHP was initially left out from AWS Lambda and other FaaS products.

In order to help I have started a monthly newsletter called **[Serverless PHP news](https://serverless-php.news/)**.

This newsletter will contain an overview of what's new regarding serverless and **how it relates to PHP**. Because some news are worth knowing about only if you can relate it to the tools you are using everyday.

If you are interested, go subscribe at:

> **[serverless-php.news](https://serverless-php.news/)**

To give you an idea what to expect you can find below a copy of the first email.

---

## Serverless PHP #1: The state of serverless PHP

Welcome to the first issue of the [Serverless PHP newsletter](https://serverless-php.news/).

First of all I want to wish you a happy new year, and thank you so much. You are more than 400 subscribed to this list and I definitely did not expect that. I hope this newsletter will live up to your expectations and I welcome any feedback (really!).

This episode is the first so I will sum up **the current state of serverless PHP** and talk about the main event of the last months: AWS re:Invent.

If you don't know anything about "Serverless" you can read an introduction in [Martin Fowler's serverless bliki entry](https://martinfowler.com/bliki/Serverless.html) or [Serverless and PHP: introducing Bref](https://mnapoli.fr/serverless-php/).

### Before AWS re:Invent

AWS re:Invent is a conference held annualy by AWS. This year (November 2018) it changed *a lot* of things, so let's start by a recap of *how PHP ran on AWS Lambda* before.

Since PHP was not officially supported on AWS Lambda the only way to run PHP was to:

- create a JavaScript lambda
- compile PHP and include the binary in the lambda
- include our PHP script (or application) in the lambda as well
- write a JavaScript script that would execute the PHP binary and proxy the HTTP request to PHP

That worked fine but this had an impact on performances. Compiling PHP and writing the JS proxy script was also impractical, which is why [Bref](https://github.com/mnapoli/bref) was created in the first place and this is what the current version (v0.2) is about. This is going to change!

### AWS re:Invent

My selection of what was announced in November by AWS that may interest you:

- [Use **any programming language on AWS Lambda**](https://aws.amazon.com/blogs/aws/new-for-aws-lambda-use-any-programming-language-and-share-common-components/) thanks to "lambda layers" and an "open runtime" API (explained in more details in the next section)
- [Cold start improvements when using RDS](https://twitter.com/jeremy_daly/status/1068272580556087296): RDS is MySQL/PostgreSQL managed by AWS. Using those with Lambda works fine except it requires to put the lambda and the database in the same VPC (private virtual network). This implies a cold start of about 5 to 10 seconds, which is a huge pain for HTTP applications. This will be solved in 2019 which, I believe, is very very good news!
- [RDS makes MySQL and PostgreSQL accessible via a HTTP API](https://aws.amazon.com/about-aws/whats-new/2018/11/aurora-serverless-data-api-beta/): this is interesting because it allows to skip managing persistent SQL connections and it is much more in line with the serverless paradigm (it is stateless). However the first version released by Amazon [is definitely not ready for production right now](https://www.jeremydaly.com/aurora-serverless-data-api-a-first-look/). AWS is known for releasing alpha services and continuously improving them so let's be patient and keep an eye on it!
- [Announcing Firecracker](https://aws.amazon.com/blogs/aws/firecracker-lightweight-virtualization-for-serverless-computing/), the "micro-VM" alternative to containers and virtual machines developped by AWS to run AWS Lambda. Their goal: the security of VMs and the low costs (startup time and overhead) of containers. You don't *need* to learn about this as AWS takes care of the details, but it is interesting to know why they are not using containers.
- [AWS SAM](https://github.com/awslabs/aws-sam-cli) has received a lot of new features which makes it a very serious alternative to [the serverless framework](https://serverless.com/) for deployments. AWS SAM is restricted to AWS but comes with very useful development tools running on Docker.
- [DynamoDB goes full serverless](https://aws.amazon.com/blogs/aws/amazon-dynamodb-on-demand-no-capacity-planning-and-pay-per-request-pricing/) via a new "pay per request" pricing: no need to reserve capacity, DynamoDB can now behave just like AWS Lambda: we can pay per request and it will scale on demand automatically.
- [DynamoDB gets transactions support](https://aws.amazon.com/blogs/aws/new-amazon-dynamodb-transactions/) for those interested in replacing their database with DynamoDB. The new API seems limited but it may help in some scenarios.
- [IntelliJ plugin to run and debug serverless applications](https://aws.amazon.com/blogs/aws/new-aws-toolkits-for-pycharm-intellij-preview-and-visual-studio-code-preview/): this will be interesting for PhpStorm users (the plugin seems to be usable only in IntelliJ for now, let's keep an eye on this)
- [Websocket support in API Gateway and AWS Lambda](https://aws.amazon.com/blogs/compute/announcing-websocket-apis-in-amazon-api-gateway/): I love how clever this is: instead of having a complex setup with long-running processes holding the connections with the clients (I'm thinking of [Laravel Echo](https://laravel.com/docs/5.7/broadcasting) for example) AWS makes it much simpler. API Gateway is responsible for maintaining the websocket connections for you. You then write code that reacts to websocket events (a new connection is made, a new message is received, etc.) via a Lambda function. In other words: back to stateless code, just like in a HTTP context. Check out [this example application built using JavaScript](https://github.com/aws-samples/simple-websockets-chat-app). I am eager to try this in a real-world use case.
- [ALB as an alternative to API Gateway for HTTP lambdas](https://aws.amazon.com/blogs/networking-and-content-delivery/lambda-functions-as-targets-for-application-load-balancers/). ALB is Amazon's Application Load Balancer and it can now be used to expose Lambdas on the web without having to use API Gateway. ALB is much simpler as there is no routes to define: it proxies everything to the lambda, and it is supposedly faster as well. There is also a difference in pricing: there is a minimum of $22 per month for ALB where API Gateway starts at $0. However for bigger applications ALB is cheaper. If you are writing websites or APIs on Lambda and pay more than $22 per month on API Gateway it may be worth investigating! Remember to share your experience!

All the re:Invent talks are online on Youtube. I can recommend [this talk about DynamoDB](https://www.youtube.com/watch?v=HaEPXoXVf2k) which was very enlightening for me about NoSQL in general and DynamoDB itself. I am tempted to try and rewrite [externals.io](https://externals.io/) using Lambda and DynamoDB to learn a bit more about it (hopefully in the coming months).

### PHP on AWS Lambda

Now that we have an official way to run PHP on AWS Lambda, let's look at how it works. The idea is that lambdas can now use **layers**. A layer is a bunch of files that will be injected in the lambda when it starts.

We can add support for PHP in AWS Lambda by creating a layer that injects the `php` binary in the lambda. But that is not enough: we need a `bootstrap`.

The `bootstrap` is the file that is called when the lambda starts. It is responsible for executing the PHP script whenever there is a new event. What's great is that the `bootstrap` file can be written in any language, including PHP.

AWS announced that a company called Stackery worked on a PHP runtime: [github.com/stackery/php-lambda-layer](https://github.com/stackery/php-lambda-layer). However this runtime is definitely not ready for production and I found it very disappointing on several levels ([I am not alone to think this](https://twitter.com/akrabat/status/1080510028531015680)).

With Bref contributors we have [benchmarked several solutions for running PHP on Lambda](https://github.com/mnapoli/bref-bootstrap-benchmarks). Those include running PHP scripts as well as HTTP applications using PHP-FPM or even PHP's built-in webserver. We have identified the best solutions and we have begun porting them in the new version of Bref.

#### Bref v0.3

The vision for [Bref](https://github.com/mnapoli/bref) has not changed: it is not just about PHP support, it is about *empowering everyone to benefit from serverless technologies*. We are hard at work on the next version (v0.3) which will provide:

- stable PHP runtimes for PHP functions as well as HTTP and console applications
- much better performances thanks to these native runtimes
- a completely rewritten and extensive documentation
- a revisited deployment process that integrates with AWS tooling
- tools for local development based on Docker (thanks to AWS SAM)
- support for any PHP application or framework thanks to PHP-FPM and the console runtime

If you are interested you can follow the [v0.3 pull request](https://github.com/mnapoli/bref/pull/113) that summarizes all changes.

### PHP outside AWS

PHP support outside of Amazon is not that great. Google Cloud Functions only supports Python and JavaScript while Microsoft Azure Functions has *experimental* support for PHP.

Zeit.co [has basic support for PHP](https://zeit.co/docs/v2/deployments/official-builders/php-now-php/) but I wouldn't consider it production-ready: it supports only 1 PHP file per application and runs it via `go-php`, a PHP 7 implementation in Go. Hopefully this will improve in the future.

[IBM OpenWhisk](https://www.ibm.com/cloud/functions) is interesting as it can run Docker containers (so it can run PHP applications). Finally an interesting alternative that may be worth exploring is Alibaba Cloud which [supports PHP functions as well as PSR-7 requests for HTTP applications](https://www.alibabacloud.com/help/doc-detail/89029.htm).

My personal belief is that AWS is years ahead of its competitors right now, especially after the latest announcements. AWS Lambda is also [very stable and reliable compared to Google Functions and Azure Functions](https://www.usenix.org/conference/atc18/presentation/wang-liang). Let's see how things evolve in the next months and years!

### Conclusion

That's it! This first episode is quite packed but a lot has been happening lately. I hope it helps you get a better overview of all this.

If you are interested in exploring the serverless world for your next big thing I hope that all of this will be useful. If you need someone to help feel free to get in touch.
