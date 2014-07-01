---
layout: post
title: "Code coverage reports with GitHub, Travis and Coveralls"
date: 2013-06-08 20:00
comments: true
external-url:
categories: php open-source continuous-integration github
---

You have a PHP project hosted on GitHub with continuous integration using Travis-CI?

How about setting up **code coverage reports**?

For example, here is the code coverage report of [PHP-DI](http://mnapoli.github.io/PHP-DI/): [![Coverage Status](https://coveralls.io/repos/mnapoli/PHP-DI/badge.png?branch=master)](https://coveralls.io/r/mnapoli/PHP-DI?branch=master) (click on the link to see the details).

<!-- more -->

To do so, you will need to create an account and enable your project at [Coveralls](https://coveralls.io/). Then add this to your `composer.json`:

```json
"require-dev": {
    "satooshi/php-coveralls": "dev-master"
}
```

Finally, update your `.travis.yml` configuration:

```yaml
language: php

php:
 - 5.3
 - 5.4
 - 5.5

before_script:
 - wget http://getcomposer.org/composer.phar
 - php composer.phar install --dev --no-interaction

script:
 - mkdir -p build/logs
 - phpunit --coverage-clover build/logs/clover.xml

after_script:
 - php vendor/bin/coveralls -v
```

Now if you commit and push, Travis will run the tests and push the code coverage results to Coverall. Check out your project page on Coverall!
