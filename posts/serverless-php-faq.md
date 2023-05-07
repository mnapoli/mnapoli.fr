---
layout: post
title: "Serverless PHP: frequently asked questions"
date: 2019-11-21 18:00
comments: true
tags:
    - serverless
---

This article is a compilation of answers to the most common questions I get about serverless PHP applications built with [Bref](https://bref.sh/).

<!--more-->

## How is it serverless when there are still servers?

Yes, I do get that question :) There are still servers involved, we just don't manage them. We don't even care about them. Servers are out of the equation, which is why the approach is called _serverless_.

## How to run serverless PHP applications locally?

For HTTP applications, like websites or APIs, Bref provides Docker images to run the stack locally. The [Bref documentation provides a `docker-compose.yml` example](https://bref.sh/docs/local-development.html#http-applications) that you can paste into your project.

For PHP functions, you can run them locally [via the `serverless invoke local` command](https://bref.sh/docs/local-development.html#php-functions).

## How to handle downtime from cloud providers?

Indeed, when running applications on cloud providers like AWS, we are exposed to their downtime if they have a major incident. Those are rare, but they do happen once in a while.

However, those large cloud providers often have less downtime than if we were running our application ourselves. It is important to keep in mind that zero downtime does not exist. The question is about minimizing risk. Would you, or your team, be able to achieve better uptime than AWS, Google, or Microsoft? And if so, is it worth the cost?

## How do costs scale in practice? Is Lambda still cheap with higher traffic?

Like all things, it depends. Smaller applications are very cheap on Lambda because their costs start at $0 (unlike a traditional server which has a fixed price). Larger applications often have more continuous traffic, so cost savings are less guaranteed. However, there are cases or large websites saving money thanks to AWS Lambda (even when migrating from an autoscaling infrastructure).

I encourage you to have a look at [cost-calculator.bref.sh](https://cost-calculator.bref.sh/), it will help you get an idea. Be careful though: at a larger scale, using AWS ALB instead of API Gateway is much cheaper, and the calculator doesn't take that into account at the moment.

## How to deal with extra costs related to DDoS attacks or traffic peaks?

AWS Lambda and related services are "pay per use". A traffic peak will result in higher costs.

There are a few ways to mitigate them. First of all, you can set billing alarms on your AWS account. These alarms monitor how much you spent this month, as well as how much you will spend by the end of the month.

Secondly, you can protect your applications from DDoS attacks using services like Cloudflare (free) or AWS Shield (expensive).

Finally, you can limit the cost impact of traffic spikes by setting limits on your application. For example, you can set the maximum number of instances your Lambda is allowed to scale up to. By default, the limit is 1000. By setting it to a lower number, like 5, you can limit the impact of a peak (at the risk of dropping requests). With a concurrency of 5, an attacker flooding a PHP website for 24 hours would cost around $7, leaving plenty of time to take action before reaching hundreds of dollars.

On top of AWS Lambda's concurrency configuration, API Gateway also has a "throttling" system. That lets you limit the number of requests per second your website or API will accept.

Everything I said above mostly applies to malicious attackers. Regular traffic spikes will usually not quadruple your AWS bill. For example, let's say you get 10 times the usual traffic on a specific day. That represents a 30% increase in your monthly bill (because all the other days in the month had the regular traffic). When this happens on my blog or on [externals.io](https://externals.io/): the $0.05 monthly bill can turn into $0.08… Nothing major. Of course, on larger websites, a 30% increase can be more expensive: you have to anticipate them in your budget. You can think of it like this: instead of paying $300/month for servers, you go with a serverless architecture that costs $50/month most of the time, and $300/month on very busy months.

## Conclusion

It is interesting to see many questions are related to billing. Moving to variable pricing is intimidating. However, all "going to serverless" experiences I've seen or heard about end up with cost savings. That doesn't mean that _all_ projects will save on costs obviously.

If you are interested, check out [bref.sh](https://bref.sh/). I can also work with you to see if serverless can be a good fit for your project, [check it out](https://null.tc/).
