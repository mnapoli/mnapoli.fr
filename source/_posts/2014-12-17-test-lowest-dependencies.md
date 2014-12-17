---
layout: post
title: "Test against the lowest Composer dependencies on Travis"
date: 2014-12-17 18:00
comments: true
categories: open-source tests composer continuous-integration
published: true
---

Composer just got a [new awesome addition](https://github.com/composer/composer/pull/3450) thanks to [Nicolas Grekas](https://twitter.com/nicolasgrekas): prefer the lowest versions of your dependencies.

```
composer update --prefer-lowest
```

This amazing option will install the lowest versions possible for all your dependencies.

What for? **Tests** of course!

<!--more-->

## Update Composer

OK before we get started, don't forget to update Composer right now if you want to use the new option:

```
sudo composer self-update
```

## Testing against lowest dependencies

It might not make a lot of sense to test an **application** against its lowest dependencies, because unless something is done wrong it will be installed with controlled versions.

However, for **libraries** (or *components*…) it's a very good thing.

To give you an example, I wanted to give it a try and ran `composer update --prefer-lowest` in [PHP-DI](http://php-di.org)'s directory: all hell broke loose… PHPUnit wouldn't even start.

The reason for this were multiples, but they all boiled down to:

- I used a feature that didn't yet exist in version X of dependency Y
- or dependency Y had a bug in version X

**and I allowed installing that problematic version X in `composer.json`**.

In the end, I was distributing a library that *could* not work at all (if people had insane version constraints).

## Setting up tests on Travis

Let's be proactive and use continuous integration to prevent that from happening again.

Here is an example of a `.travis.yml` configuration that would run the tests using the lowest dependencies with PHP 5.3.3:

```yml
language: php

php:
  - 5.3.3
  - 5.4
  - 5.5
  - 5.6

matrix:
  include:
    - php: 5.3.3
      env: dependencies=lowest

before_script:
  - composer self-update
  - composer install -n
  - if [ "$dependencies" = "lowest" ]; then composer update --prefer-lowest --prefer-stable -n; fi;

script:
  - phpunit
```

You'll notice that we have to run `composer self-update` because Travis hasn't picked up the latest Composer version yet.

Here was the result on Travis:

{% img /images/posts/composer-lowest-dependencies.png %}

A new job was added to the build matrix, and this job ran on PHP 5.3.3 with the lowest dependency versions.

If you are interested, have a look at [the pull request](https://github.com/mnapoli/PHP-DI/pull/219).
