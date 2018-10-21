---
layout: post
title: "Overriding dependencies with Composer"
date: 2013-04-16 20:00
comments: true
external-url:
categories: composer github myclabs
---

At my company, **My C-Sense**, we use Doctrine amongst other PHP frameworks and libraries. When we find bugs (or need new features), we contribute to the project through our [open source initiative](https://github.com/myclabs).

The problem is when we submit a pull request on Github, several months usually happen until our fix appears in a stable release.

To be able to enjoy our bugfixes immediately, here is our workflow:

- We fork the repository of the project to [our organization account](https://github.com/myclabs)
- We commit and publish the bugfix in a branch of our repository
- We submit a Pull Request
- We override the dependency to the project with our version in Composer

Overriding a dependency [is quite simple](http://getcomposer.org/doc/04-schema.md#repositories): just add your git repository in your `composer.json` and require you branch.

But when we want to override, for example, `doctrine/common` which is used by `doctrine/orm`, then we have a problem: `doctrine/orm` wants a stable version of `doctrine/common`, it will conflict with your requirement to a dev branch.

The solution is to **alias your dev branch to a stable release**, and that is possible through the awesome “[inline alias](http://getcomposer.org/doc/articles/aliases.md#require-inline-alias)” functionality in Composer.

<!-- more -->

Here is an example:

```json
{
    "require": {
        "doctrine/orm": "2.3.*",
        "doctrine/common": "dev-ChainDriverFix as 2.3.0"
    },
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/myclabs/common.git"
        }
    ]
}
```

Here, our branch `ChainDriverFix` will override the 2.3.0 version of `doctrine/common`, which will also be compatible with `doctrine/orm`!
