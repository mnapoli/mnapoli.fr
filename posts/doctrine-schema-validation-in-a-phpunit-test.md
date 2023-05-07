---
layout: post
title: "Doctrine schema validation in a PHPUnit test"
date: 2012-12-10 20:00
comments: true
external-url:
categories: doctrine phpunit
---

Doctrine offers a [command line option to validate the schema](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/tools.html#runtime-vs-development-mapping-validation) (or mapping):

```
./doctrine orm:validate-schema
```

This is very useful, when I ran it against my code, which was *working* by the way, I got several errors/warnings.

However, I didn’t want to have to run this tool manually once in a while. I already have tests for that. So I thought: **why not integrating the schema validation to the tests!**

<!-- more -->

So here is my implementation of a PHPUnit test failing when Doctrine validation find errors:

<script src="https://gist.github.com/mnapoli/4249675.js"></script>
