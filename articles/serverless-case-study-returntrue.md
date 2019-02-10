---
layout: post
title: "Serverless case study: returntrue.win"
date: 2018-10-29 18:00
comments: true
image: /images/posts/returntrue-stats.png
---

*This article is part of a series of case studies of serverless PHP applications built with [Bref](https://github.com/mnapoli/bref) on AWS Lambda. If you are not familiar with serverless and Bref, I invite you to read [**Serverless and PHP: introducing Bref**](/serverless-php/).*

This case study is about the [returntrue.win](https://returntrue.win/) website. This website was my first serious experiment with serverless and *Function as a Service*.

<!--more-->

[Returntrue.win](https://returntrue.win/) is a "puzzle game for PHP developers". A piece of PHP code is shown. Users can fill the missing parameters and run the code. The goal is to make the code return `true`, then the user can move on to another level.

[![](/images/posts/returntrue-screenshot.png)](https://returntrue.win/)

Not sure how to get the best scores? Check out [this complete solution](https://www.rpkamp.com/2018/02/15/all-answers-to-returntrue.win-with-explanations/).

## Architecture

The website is built using the following services:

- AWS Lambda for running PHP
- API Gateway to expose the lambdas via HTTP

  This service is required if you want to make a lambda accessible on the internet as an API or website. [Bref](https://github.com/mnapoli/bref) takes care of configuring API Gateway for you.
- DynamoDB to store the best scores

I chose DynamoDB because I wanted to discover that service and because it was cheaper than RDS. At first I wanted to store the data on disk in a JSON file but the distributed approach of lambdas, and the fact that the filesystem is read-only, put a stop to that. In the end I was happy with that choice and it cost me nothing. But again I stored so little in there that its usage isn't really significant.

There are 2 lambdas:

- the first lambda is the website
- the second lambda runs the code submitted by users (I like to call it "eval-as-a-service")

This separation helps ensure that code submitted by users will not affect the website, even if it crashes.

![](/images/posts/returntrue-flow.png)

I soon discovered that building a website with AWS lambda is a bit trickier than building an API. The whole thing is not built to handle assets (CSS, JavaScript…) out of the box. A simple solution is to put assets on a CDN such as AWS S3. Since my needs were very simple I decided to include Bootstrap from a public CDN and write a few lines of inline CSS. Not the cleanest solution but that just worked.

## Traffic

I released [returntrue.win](https://returntrue.win/) in February 2018 and it got a bit of attention on [Twitter](https://twitter.com/matthieunapoli/status/959918744213573635) and [Reddit](https://www.reddit.com/r/PHP/comments/7x92e6/return_true_to_win/). The website received a decent amount of traffic, especially the first days where I watched with attention the lambdas scale up and down.

As you would expect, I did not have anything to do except let Amazon take care of running my code for me.

Over the first month, the website served **400,000 HTTP requests** which resulted in **650,000 lambda executions** (remember that a user running a piece of code means 2 lambda executions: the website plus the "eval-as-a-service").

![](/images/posts/returntrue-stats.png)

## Costs

As a developer I enjoyed not worrying about whether my server would be able to handle the traffic. However I have to admit I was worried about how much this would cost me in the end :)

In total, the first month **cost me $3**.

Needless to say I was relieved! The website has been running since and I pay close to $0 every month.

The majority of the cost was related to API Gateway as AWS Lambda has a generous free tier. Let's break it down:

- AWS Lambda: $0 thanks to the free tier, would have paid $2.78 otherwise
- API Gateway: $2.39:
    - $2.23 for the HTTP requests
    - $1.16 for the 1.8Gb of data transfer
- DynamoDB: $0 thanks to the free tier, would probably be around $0.6 without the free tier
- AWS CloudWatch: $0
- Data Transfer: $0.04 - I still don't understand completely what this is about
- Taxes: $0.5 :)

## Conclusion

While I hope this case study is useful, there are many things that could be improved:

- storing assets on a CDN
- using a real PHP framework
- caching HTTP requests (many many requests could be cached, resulting in an even lower cost)

And the website could definitely have a better design. I hope to open source the website at some point to allow users to contribute new levels.
