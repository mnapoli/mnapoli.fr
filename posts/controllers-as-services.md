---
layout: post
title: "Controllers as services?"
date: 2013-07-01 20:00
comments: true
external-url:
categories: dependency-injection php-di
---

This post is sort-of a response to the [blog post of Benjamin Eberlei about Controllers in Symfony 2](http://www.whitewashing.de/2013/06/27/extending_symfony2__controller_utilities.html).

The subject is about Controllers and their dependencies:

> Controllers as a service are a heated topic in the Symfony world. Developers mainly choose to extend the base class, because its much simpler to use and less to write.

With Symfony 2, you can write controllers 2 ways:

1. extend the base Controller class. This is simpler and more practical but it ties up your controller to Symfony. Also, to fetch dependencies, you have to get them from the container, which is known as the **Service Locator anti-pattern** (= bad).

2. create a “normal” class, and use it as a service. That means you can use dependency injection through the constructor to get your dependencies. This is clean, this looks good, but you end up with managing a lot of dependencies :/

To ease up solution n°2, Benjamin proposes to create a “ControllerUtility” class which would group the most used controller services. That way, you dramatically reduce the dependencies, and still hide the container.

I use a different solution.

<!-- more -->

## Constructor injection is not the only possible injection

The idea is to keep the solution n°2, but use **Property Injection** instead of Constructor Injection.

Property injection is generally frowned upon, and for good reasons:

- injecting in a private property breaks encapsulation
- it is not an explicit dependency: there is no contract saying your class need the property to be set to work
- if you use annotations to mark the dependency to be injected, your class is dependent on the container

**BUT**

if you follow best practices, **your controllers will not contain business logic** (only routing calls to the models and binding returned values to view).

So:

- you will not unit-test it (that doesn’t mean you won’t write functional tests on the interface though)
- you may not need to reuse it elsewhere
- if you change the framework, you may have to rewrite it (or parts of it) anyway (because most dependencies like Request, Response, etc. will have changed)

Because of that, I chose to use Property injection.

## Property injection

Here is what my controllers look like:

```php
<?php
use DI\Annotation\Inject;

class UserController
{
    /**
     * @Inject
     * @var RouterInterface
     */
    private $router;
 
    /**
     * @Inject
     * @var FormFactoryInterface 
     */
    private $formFactory;
 
    public function createForm($type, $data, $options)
    {
        // $this->formFactory->...
    }
}
```

Note this is an example using [PHP-DI](http://mnapoli.github.io/PHP-DI/), my alternative DI Container. It allows to mark injections using annotations.

I know many PHP devs don’t like annotations, and there are some reasons not to use it. But in this case, because of the points I explained above, I find it acceptable to use the `@Inject` annotation. I find it also extremely practical.

Of course, this example also applies without using annotations (using a configuration file f.e.), and **it also applies to Symfony’s container**.

In the end:

- controllers don’t use the container
- controllers can be reused elsewhere, given they are fetched through the container
- we have full auto-completion and refactoring support in IDEs
- **controllers are easy and fast to write and read** (and that’s something I value a lot)

By the way, some Java developers may find this pattern of code familiar, it’s inspired from when I was working with Spring :)

## Performance note

> You are injecting services that may not be used

[PHP-DI](http://php-di.org/) and [Symfony DIC](http://symfony.com/doc/current/components/dependency_injection/index.html) both support lazy injection, i.e. injecting a proxy that will load the target service only when it is used.
