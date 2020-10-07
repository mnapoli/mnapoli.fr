---
layout: post
title: "Serverless and PHP: Performances"
date: 2018-05-24 12:00
comments: true
image: https://mnapoli.fr/images/posts/serverless-performances.png
tags:
    - serverless
---

Last week I introduced [Bref as a solution to running PHP serverless](/serverless-php/).

---

**Update: Since November 2018 AWS Lambda supports PHP via *custom runtimes*. Bref now takes advantage of that, [you can read more about it at bref.sh](https://bref.sh/).**

**Performances have changed a lot with these new versions.** This article is now obsolete.

---

Today let's explore what performances to expect when running PHP on AWS lambda using [Bref](https://github.com/mnapoli/bref). Everything shown in this article is [open source on GitHub](https://github.com/mnapoli/bref-benchmark) and can be reproduced.

Everything shown here is specific to AWS Lambda. Other serverless providers (Azure, Google Cloud…) could offer different performances and costs.

<!--more-->

## Results

This is a comparison of the **HTTP response time** that I got by calling the lambdas from an EC2 machine in the same region, on both a NodeJS lambda and a PHP lambda. This is not the lambda's execution time but the real HTTP response time, network included. If you want to use lambdas in a non-HTTP context those results will still be useful.

I run the test more than 3 times for each combination and I keep the lowest one (because of cold starts numbers vary a lot at first).

I ran the test for several memory sizes: lambdas "sizes" are configured through the memory. More memory means more CPU, but also higher costs.

> AWS Lambda allocates CPU power proportional to the memory by using the same ratio as a general purpose Amazon EC2 instance type, such as an M3 type. For example, if you allocate 256 MB memory, your Lambda function will receive twice the CPU share than if you allocated only 128 MB.

| Memory | Node lambda | PHP lambda |
| ------ | ----------- | ---------- |
| 128M   | 28ms        | 340ms      |
| 512M   | 28ms        | 86ms       |
| 768M   | 21ms        | 59ms       |
| 1024M  | 20ms        | 46ms       |
| 2048M  | 21ms        | 42ms       |
| 3008M  | 21ms        | 46ms       |

![](/images/posts/serverless-performances.png)

We can see that Node performances are pretty consistent, whereas PHP performances become optimal above 1024M. Performances with 512M can be acceptable depending on the use case (e.g. workers, crons, etc.). 128M performances are pretty poor.

**To sum up, we should expect a 20ms penalty to using PHP over AWS Lambda (using [Bref](https://github.com/mnapoli/bref)) compared to other languages.**

What's interesting to note is that Node's 21ms base response time is because of the HTTP layer (API Gateway and network). Let's consider this other graph (Cloudwatch metrics):

![](/images/posts/serverless-cloudwatch.png)

- blue line: HTTP response time (~40ms for PHP, 13ms for Node)
- green line: PHP execution time (25ms)
- orange line: Node execution time (0ms)

This confirms that PHP adds 20ms-25ms to the lambda's execution time. The HTTP layer (API Gateway) adds ~15ms in all cases. The network between the lambdas and the EC2 machine used for the tests accounts for ~5ms.

## Cold starts

The numbers above are the "cruise" numbers, when everything is warmed up and runs smoothly. But lambda cold starts should not be ignored. I will not cover that topic again since it has been covered heavily elsewhere, I can recommend reading [this article](https://hackernoon.com/im-afraid-you-re-thinking-about-aws-lambda-cold-starts-all-wrong-7d907f278a4f).

Here are measurements made on Node and PHP lambdas, completed with [cold starts from other languages found in this comprehensive article](https://read.acloud.guru/does-coding-language-memory-or-package-size-affect-cold-starts-of-aws-lambda-a15e26d12c76).

| Memory | Python | Node | PHP    | Java   | C#     |
|--------|--------|------|--------|--------|--------|
| 128M   |    1ms | 21ms | 1261ms | 3562ms | 4387ms |
| 512M   |    0ms |  3ms |  336ms |  999ms | 1223ms |
| 768M   |        |  2ms |  231ms |        |        |
| 1024M  |    0ms |  2ms |  210ms |  530ms |  524ms |

![](/images/posts/serverless-coldstarts.png)

**PHP's cold start is lower than Java and C#** even though both those languages are supported natively by AWS Lambda. From 768M and up the cold starts stabilize around 230ms.

We can also see that Python has lower cold starts than Node. Bref could switch to Python as the language used to invoke PHP but the gain seems to be minimal compared to PHP's execution time.

## Costs

Let's overlay the PHP performances and the cost associated. For simplicity of reading, and because the base response time is 340ms, I will be counting 1 million lambda requests that take 400ms to execute.

**Note that this do not include the cost of API Gateway, data transfers, etc.** (which can be much higher than that). I also do not include the free tier because the goal is to compare each row with the others.

I have removed 20ms of the execution time to go from the HTTP response time to the actual lambda's execution time.

| Memory | Base execution time | Total cost for 1M executions of a 400ms lambda | Cost of the base execution time |
| ------ | ------------------- | ---------------------------------------------- | ------------------------------- |
| 128M   | 320ms               | $1                                             | $0.67 for 320ms                 |
| 512M   | 66ms                | $3.5                                           | $0.55 for 66ms                  |
| 768M   | 39ms                | $5.2                                           | $0.49 for 39ms                  |
| 1024M  | 26ms                | $6.87                                          | $0.43 for 26ms                  |
| 2048M  | 22ms                | $13.5                                          | $0.74 for 22ms                  |
| 3008M  | 26ms                | $19.7                                          | $1.29 for 26ms                  |

Using the 1024M lambdas to get great performances with PHP mean a 6 times increase on costs. **However this does not take into account that lambdas will also run faster, bringing costs down**, which is what we can see in the last column (cost associated to the base execution time is actually decreasing). On the other hand most applications will not run 6 times faster because often the CPU is not the most limiting factor (this is rather I/O like network, databases…).

Like in all situation it is best to make your own tests to get a better idea. If your lambda is invoked through HTTP the major cost will be API Gateway anyway.

## Conclusion

- use 512M memory for acceptable performances, 1024 and up for optimal performances
- expect a 20ms penalty to using PHP over AWS Lambda compared to other languages
- PHP's cold start is lower than other languages like Java and C#, from 768M and up the cold start stabilizes around 230ms

Want to get started: have a look at [Bref](https://github.com/mnapoli/bref).

Update: I realize I did not mention opcache which is an important player in PHP performances. I did not forget about it, out of the box it is not supported because PHP is running as CLI. However I am planning to work on that, opcache can be used in CLI processes.
