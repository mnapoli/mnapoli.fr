---
layout: post
title: "HTTP performances with Bref v0.3"
date: 2019-02-10 18:00
comments: true
image: https://mnapoli.fr/images/posts/bref-0.3-performance-returntrue.png
---

Back in October I published a [case study of a serverless website: returntrue.win](/serverless-case-study-returntrue/). This website runs on AWS Lambda using [Bref](https://bref.sh/).

A new major version of Bref (v0.3) was released this month and it comes with [completely new internals](https://github.com/mnapoli/bref/releases/tag/0.3.0). I was curious, as many others were, of what it meant for performances.

<!--more-->

I benchmarked 2 websites:

- the current [returntrue.win](https://returntrue.win/) website running with **Bref 0.2**
- the same website running with **Bref 0.3**

I made sure both lambdas were using the same configuration, which is **1024M** of RAM. For those not aware, the amount of RAM available [is proportional to the CPU power](https://docs.aws.amazon.com/lambda/latest/dg/resource-model.html) allocated to the lambda.

The page that we are testing is running with a custom PHP framework (not optimized, no cache), uses Twig without cache and performs 1 query to DynamoDB.

It is important to note as well that I am reporting the execution time of the lambda (i.e. the application), not the whole HTTP response time. Since we use API Gateway we need to consider that API Gateway adds about 15ms to lambda's execution times.

## Cold starts

[Cold starts](https://hackernoon.com/cold-starts-in-aws-lambda-f9e3432adbf0) happen when a new lambda instance is provisioned. By triggering around 20 cold starts for each function I did not see a difference.

In both cases cold starts where around **650ms**.

That's a bit disappointing, hopefully we can improve this a bit. I did some tests with 2048M of RAM and cold starts where brought down to 450-500ms.

## Warm results

After testing the cold starts I did performance tests on warm lambdas by running:

```bash
ab -c 1 -n 500 https://returntrue.win/
```

|  | Average | Maximum | Minimum |
|-----------|:-------:|:-------:|:-------:|
| Bref v0.2 | **250ms** | 320ms | 220ms |
| Bref v0.3 | **39ms** | 106ms | 21ms |

This is very good news: the website runs **6 times faster** on average!

With an average response time of 39ms instead of 250ms, building website or APIs on AWS Lambda becomes a lot more interesting. You can check out below the detailed graph to see how results spread out:

[![](/images/posts/bref-0.3-performance-returntrue.png)](/images/posts/bref-0.3-performance-returntrue.png)

- red lines: Bref 0.2 (minimum, average, maximum)
- green lines: Bref 0.3 (minimum, average, maximum)

I did some tests again with 2048M of memory and results improved even more: 35ms of average response time, with a minimum of 20ms and a maximum of 65ms.

## Conclusion

These results should be taken with a grain of salt as we are testing a small application here. I intend to do more tests in the near future, and I encourage others to do some as well.

However this is very encouraging! Being able to run PHP applications under 100ms on AWS Lambda is great news as it continues to open more possibilities.

As a side note I have now deployed [returntrue.win](https://returntrue.win/) on Bref v0.3.

And if you want to give a try to Bref head over to [bref.sh](https://bref.sh/).
