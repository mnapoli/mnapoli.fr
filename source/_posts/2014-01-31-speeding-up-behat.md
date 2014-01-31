---
layout: post
title: "Speeding up Behat"
date: 2014-01-31 18:00
comments: true
categories: behat
published: false
---

At [My C-Sense](http://www.myc-sense.com), we make heavy use of Behat to guarantee a good level of quality before releasing
a new version in production. However, our test suite was longer than 7 hours, which would mean we could only run it
once a day.

That is a problem, because then you can only run your test suite against one branch, once a day. And of course, the branch
you want to test the most is the one which is going to end up in production soon, i.e. the most stable.
So you quickly fall in pain chain where:

1. your development branch is not tested with Behat because it is used for the stable branch
2. when that branch is merged to the stable branch, Behat will scream and half of your scenarios will fail
3. you will spend days fixing scenarios and bugs, thus keeping Behat on the "stable" branch all the while (and away from the development branch)
4. goto 1

We were tempted making the effort to set up a second machine for Behat which would run against the next unstable branch,
but instead I spent some time trying to optimize the whole suite to see where that could get us.

So this is what this post is about: **optimizing your Behat suite to make it run as fast as possible**.

<!--more-->

## Caches

## Database

## Browser

## Running tests in parallel?
