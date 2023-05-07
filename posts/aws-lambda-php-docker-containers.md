---
layout: post
title: "AWS Lambda can now run PHP using Docker Containers"
date: 2020-12-01 18:00
isPopular: false
comments: true
tags:
  - serverless
---

AWS Lambda [now supports running Docker containers](https://aws.amazon.com/fr/blogs/aws/new-for-aws-lambda-container-image-support/)!

**This is big news for the PHP community**: while [Bref](https://bref.sh/) runtimes provide out-of-the-box support for PHP on AWS Lambda, we can now run any container image 🎉

Let's check that out!

<!--more-->

## Lambda runtimes vs. containers

Here are the different ways to run code on AWS Lambda:

1. Using an official Lambda runtime:
    - runs on Amazon Linux
    - supports only a few languages, like JavaScript, Java, Python, etc. but **not PHP**
2. Using a custom Lambda runtime:
    - runs on Amazon Linux
    - the custom runtime is imported via a "Lambda layer" (i.e. a zip file unzipped in `/opt`)
    - the custom runtime is built upon the official Lambda "Runtime API"
    - can support any language we want
    - **This is what Bref currently provides**: custom runtimes to run PHP on Lambda
3. Using a Docker image (**new**)
    - runs on the Linux version you want
    - with the programs and libraries you want
    - can support any language
    - must be made compatible with the official Lambda "Runtime API" (like custom runtimes)

The last point is important: Lambda is still not made for running daemons and web servers, like Apache, Nginx, etc (which doesn't mean we can't [run web applications on Lambda](https://bref.sh/docs/runtimes/http.html)).

This is a good thing: Lambda's execution model is event-driven, making it extremely scalable and cost-efficient.
We can use any container image, but we still need to "bridge it" with the Lambda Runtime API.

The good news is that Bref provides the "runtime client" for PHP.

## Docker support in Bref

One of the perks of being an [AWS Serverless Hero](https://aws.amazon.com/developer/community/heroes/matthieu-napoli/) is that I get early access to new features :) I was able to test things beforehand and make sure that Bref was compatible.

On top of the PHP Lambda runtimes, Bref provides [Docker images](https://hub.docker.com/u/bref) that mirror the Lambda environment:

- `bref/php-80`
- `bref/php-74`
- `bref/php-80-fpm`
- `bref/php-74-fpm`
- etc.

Using those images as a base for your application is the fastest way to get started: the runtime client is already included in these images.
That means that they work out of the box on Lambda. Check out this `Dockerfile` for example:

```dockerfile
FROM bref/php-80-fpm

# Include any extension we want, for example:
#COPY --from=bref/extra-gd-php-73:0.9.5 /opt /opt

# Copy our code in the container
ADD . $LAMBDA_TASK_ROOT

CMD [ "index.php" ]
```

In the coming weeks, we will explore the possibility of using *any* Docker image, including the official PHP Docker images.

## What about performances/execution time/deployment size…?

The 15-minutes maximum execution time still applies with Docker containers.

According to AWS, performances should be similar to native runtimes. In my tests, warm invocations were as fast as usual.
Cold starts on small Docker images were as fast as native runtimes. I've seen slower cold starts (1-2 seconds) on larger images.
We'll be aggregating feedback over the next weeks because it's definitely too early to tell.

The good news is that while Lambda functions are limited to 250MB, containers can be up to 10GB. That will certainly help when deploying large monoliths to Lambda.

One limitation to keep in mind is that after 14 days of inactivity, a container-based Lambda function will switch to an "INACTIVE" state. In that state, it takes a few seconds for the Lambda to re-activate, which causes a very slow cold start. I don't expect this to be a problem on large projects, but keep that in mind.

## Which one to choose?

You may have understood now that "Container support" in Lambda mostly means that you can use Docker to package your application.

It does not involve radical architecture changes for us; our code runs the same way in Docker as in native runtimes: in an event-driven way.

It is a bit early to be definitive about this, but here is my recommendation as of now:

- **use Lambda runtimes by default**:
  - these are simpler to use and well documented
  - no Dockerfile to create (you don't even need to _know_ Docker)
  - with Bref, Lambda runtimes are automatically versioned in sync with the Bref version, making it much easier to keep up to date and upgrade
  - 3rd party tools support Lambda runtimes, most of them don't support containers yet
- use Docker if you have a specific reason for that:
  - you need to deploy an application larger than 250MB
  - you want control over the Linux image
  - you want to include an exotic PHP extension
  - you want to include specific system libraries or programs

Docker provides more freedom to support more use cases on Lambda. However, as far as I can tell, it is not a de-facto replacement for AWS runtimes.

## Example: deploying your first PHP container using Bref

**Warning: this example is advanced.**
If you don't know Bref, [get started via its documentation](https://bref.sh/docs/).
The example assumes you know about the current Bref runtimes.

Since the feature is brand new, you will need to set up everything manually.
This is **not representative** of how containers will be supported eventually (we need to wait for container support in the Serverless framework).
This example is just so that early-adopters can have a bit of fun.

### Creating the Docker image

To deploy a web application using PHP-FPM ([the FPM runtime](https://bref.sh/docs/runtimes/http.html)), we can use a `Dockerfile` like this:

```dockerfile
# Uses PHP 8.0, feel free to use php-74-fpm if you prefer
FROM bref/php-80-fpm
ADD . $LAMBDA_TASK_ROOT
CMD [ "index.php" ]
```

`index.php` is the front controller of the application. For the example, let's keep it simple:

```php
<?php
echo 'Hello world!';
```

To deploy an event-driven function instead ([the Function runtime](https://bref.sh/docs/runtimes/function.html)), we can use a `Dockerfile` like this:

```dockerfile
# Uses PHP 8.0, feel free to use php-74 if you prefer
FROM bref/php-80
ADD . $LAMBDA_TASK_ROOT
CMD [ "function.php" ]
```

`function.php` is the function to invoke:

```php
<?php
return function () {
    return 'Hello world!';
};
```

As you can see, thanks to the Docker images provided by Bref it's extremely simple!

### Deploying

**Update: the Serverless Framework [now supports deploying containers directly in `serverless.yml`](https://www.serverless.com/blog/container-support-for-lambda), which is much simpler than the process described below.**

Let's deploy that:

1. Create a Docker image on AWS:

    - open the [ECR Console](https://console.aws.amazon.com/ecr/home)
    - click "Create repository"
    - set the name of your image (e.g. `app`) and validate

2. Push your container image to AWS:

    - in the [ECR Console](https://console.aws.amazon.com/ecr/home), open the "repository" (or image) that you created
    - click "View push commands"
    - run the commands that are displayed

3. Create a Lambda function:

    - open the [Lambda Console](https://console.aws.amazon.com/lambda/home#/functions)
    - click "Create function"
    - select "Container image"
    - set a function name (e.g. `app`)
    - set the container image by clicking "Browse Images"
    - click "Create function"

The function is now ready to be invoked.

If you are setting up a web application (using the "FPM" runtime), you will need to set up API Gateway. The tutorial stops here: as I said it is meant to illustrate what's different with containers and guide advanced users (who know how to set up API Gateway).

In the next weeks Bref will natively support containers, so all those steps will be irrelevant.

## Conclusion

That's it! If you have any question, ask me at [@matthieunapoli](https://twitter.com/matthieunapoli).

Don't forget to read [the official announcement from AWS to learn more](https://aws.amazon.com/fr/blogs/aws/new-for-aws-lambda-container-image-support/).

If you want to get started, [check out Bref](https://bref.sh/) and [Serverless Visually Explained](https://serverless-visually-explained.com/).

If you need professional support, [get in touch here](https://bref.sh/#enterprise).
