---
layout: post
title: "Running composer install when you switch branch"
date: 2013-09-09 17:03
comments: true
categories: git composer
---

When working with [Composer](http://getcomposer.org/) and git branches, you will end up either:

- reinstalling dependencies each time you switch branch
- or meeting weird bugs because you didn't

because `composer.json` may have changed between branches.

To have composer automatically re-install dependencies when you switch to a branch, simply create a `.git/hooks/post-checkout` file in your project repository:

```sh
#!/bin/sh

cd $GIT_DIR/..

composer install
```

This is a git post-checkout hook (as the name suggest)
