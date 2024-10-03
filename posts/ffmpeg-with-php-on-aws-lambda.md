---
layout: post
title: "Using FFmpeg with PHP on AWS Lambda"
date: 2024-10-03 18:00
isPopular: false
comments: true
tags:
  - serverless
---

You can use FFmpeg with PHP on AWS Lambda. Here is how to do it with [Bref](https://bref.sh/).

<!--more-->

## Switching to deploying containers on AWS Lambda

The default approach to deploying PHP to AWS Lambda with Bref is to deploy via zip files, while the PHP runtime is provided by Bref via AWS Lambda layers. This is great because deployments are fast and simple. However, adding custom binaries to the Lambda environment is hard because you have to create your own "layers".

To add FFmpeg to Lambda, we can switch to deploying a container image to Lambda. This way we have all the familiar tools to install FFmpeg in the container image later on.

There is a [Bref guide](https://bref.sh/docs/deploy/docker) on how to deploy a custom container image to Lambda, but here's the short version:

1. we need a `Dockerfile`:

```bash
FROM bref/php-82-fpm:2
 
# Copy our source code in the image
COPY . /var/task
 
# Configure the handler file (the entrypoint that receives all HTTP requests)
CMD ["public/index.php"]
```

2. We change `serverless.yml` to deploy our container image:

```yaml
service: myapp
 
provider:
    name: aws
    ecr:
        images:
            myimage:
                # Path to the `Dockerfile` file
                path: ./
 
functions:
    website:
        image:
            name: myimage
        events:
            - httpApi: '*'
```

Deploying is the same command as before: `serverless deploy`.

## Installing FFmpeg in the container image

Now that we have a container image, we can install FFmpeg in it using [multi-stage builds](https://docs.docker.com/build/building/multi-stage/):

```bash
# This is Bref's "build" image that we can use to build custom binaries and extensions
FROM bref/build-php-82:2 as build

# Install ffmpeg
RUN set -xe; \
    mkdir -p /tmp/ffmpeg; \
    curl -Ls https://johnvansickle.com/ffmpeg/releases/ffmpeg-release-amd64-static.tar.xz \
    | tar xJC /tmp/ffmpeg --strip-components=1
RUN mv /tmp/ffmpeg/ffmpeg /opt/bin/ffmpeg

FROM bref/php-82-fpm:2

# Copy what we built above in our final image
COPY --from=build /opt /opt
 
# Copy our source code in the image
COPY . /var/task
 
# Configure the handler file (the entrypoint that receives all HTTP requests)
CMD ["public/index.php"]
```

The `ffmpeg` binary is now available in the `$PATH` of the Lambda environment, PHP can use it like any other binary:

```php
exec('ffmpeg -i input.mp4 output.mp4');
```
