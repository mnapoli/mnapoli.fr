---
layout: post
title: "Dependency Injection with PHP"
date: 2012-09-20 20:00
comments: true
external-url:
categories: php dependency-injection php-di
---

I used to develop using Singletons, registries or even static classes. Those days are gone.

I decided to use **Dependency Injection** so that:

- my classes would be testable
- replacing an implementation by another would be not only doable, but easy (and so extending a library/module would too)
- the design of those classes wouldn’t be guided by the question of “how they will be used”
- my code would be cleaner, simpler
- and IDE auto-completion/type-hinting would always work

I gave a try to Symfony and ZF2 DI systems, but they both seem way too complicated for just a simple need (that anyone who has worked with Spring would understand):

```php
class MyClass {
    /**
     * @Inject
     * @var MyService
     */
    private $service;
}
```

This short code means: *Inject, using a simple annotation, an instance of another class into a property*.

I started working on a framework enabling such functionality few months ago. It is now in a mature state. It is based on the [Annotations library of Doctrine 2](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html), and takes most of its ideas of Spring 3.

You can check out this framework on its official website: [PHP-DI](http://mnapoli.github.com/PHP-DI/), and you are welcome to use it or contribute.
