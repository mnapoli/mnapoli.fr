---
layout: post
title: "From a ContainerInterface to world domination"
date: 2013-12-29 18:00
comments: true
categories: php dependency-injection
published: false
---

Ready for a stretch? This is a (probably unrealistic) idea about dependency injection containers and an evil plan for world domination. Well, maybe not world domination, but crazy **framework interoperability** allowing one to **use several web frameworks at the same time**.

<!--more-->

## ContainerInterface

What I mean by `ContainerInterface` is a PHP interface that has been discussed here and there (PHP-FIG, Acclimate, PHP-DI, Mouf …). The interface looks like this:

```
interface ContainerInterface
{
    public function get($identifier);
    public function has($identifier);
}
```

This interface *tries* to standardize the basic usage of a dependency injection container, or at least the usage made by a framework: reading entries from the container.

But what I also mean by `ContainerInterface` is what it represents: **the basis for dependency injection containers interoperability**.

Such an interface (or other derived interfaces) could allow to replace Framework A's container by another container. It could also allow to chain containers together, so that instead of replacing the framework's container, you could use 2, 3 or more containers at the same time.

*By the way, if you are interested about all this, have a look at [container-interop](https://github.com/container-interop/container-interop). This is a group in which we are trying to propose usable versions of those container interfaces.*

## Why container interoperability?

So, why should anyone care about `ContainerInterface` and all those abstract ideas?

### Choose your container

The most obvious answer is simply that it let's you use *any* container. Just like [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) allowed you to choose your own logger, such an interface would allow you to look at all containers and choose the own you want based on their features.

And given containers are used both ways (set entries & read entries), and that only the "reading" part needs standardization for interoperability (as meant by the previous section), that allows a lot of liberty on the "set entries" part.

For example, Symfony's container uses a YAML file to define services, whereas it's plain PHP for Zend Framework's container. PHP-DI allows configuration files, but also proposes autowiring and annotations. So there's choice.

### Portable configurations

Allowing one to use *any* container in *any* framework is a very cool idea.

But the coolest thing IMO is most certainly that you can write *portable* configurations. Maybe the term is not right, but what I mean is that the container configuration you wrote for your Symfony app is also readable by your ZF app.

## The container at the center of your application

The thing is that your container configuration is actually your application configuration. Configuring your logger, you DB layer, your email services, your domain services, … All of this can be done through a container.

And the day you want to use Laravel instead of ZF, you may still want to use the same logger, the same DB layer, the same email services and you would still want to have your domain services configured. Having to rewrite all that configuration is a pain, and it doesn't make sense.

## Application frameworks are not MVC frameworks anymore

*needs a transition*

What part of your app really depends on your framework?

Once upon a time, we had MVC frameworks. The Model was coupled with the framework because the ORM was **in** the framework. The Views and Controllers were of course strongly coupled to the framework too. Switching from one framework to another would mean rewriting almost everything.

Today, modern frameworks are **application frameworks**, and they are (almost all) built on independent and reusable components.

So libraries like ORMs, loggers, email services, … are components decoupled from the framework. You can use them without having to use the framework. So your model and your services shouldn't be coupled with your framework.

Your views can be decoupled with the framework too. For example, you could use a templating engine like Twig, and have extensions that provide integration with other components (the router to build urls, forms, …).

Your controllers could be the part the most coupled to the framework, since it takes a request and returns a response, both being defined by the framework. Of course, you could abstract these or write adapters that would decouple your controllers from the framework, but since controllers are supposed to be slim anyway (no domain logic), I'm not even sure that would be worth the effort.

So the part of your app that is coupled with the framework you use is the controller and anything before it, i.e. the router. The router part is usually a configuration file

## Bundles are cool, interoperability is cooler

Symfony offers the concept of *bundles*, which is basically a way to distribute an application module (admin back-end, user login/logout pages, …). You can then integrate this module to your application in a few seconds. And this is awesome.

But I find it too bad that to take advantage of these bundles, you have to write you application using Symfony. Same if you want to write bundles: you have to use Symfony.

Wouldn't it be great if anybody could write application modules in their framework of choice, and have them usable with any other framework? The "bundle" or module would only have its routing/controller layer coupled with the underlying framework. It could provide framework-agnostic models or services for you to use in your other modules.

To make these models and services available throughout the application, the module would come with its container and its configuration. The module's container could be chained to the application's container for example (and we are back on container interoperability).

## World domination

So, to conclude, what do I mean by *world domination*? Of course, not actual world domination, but some sort of holy grail of a state where:

> you can divide your application in modules using different frameworks

That's extremely seducing:

- different teams could write different modules with their own preferred technologies. For example, a micro-framework for the front-end, a full-stack framework for the backend
- in the same vein, you could integrate in one application modules developed by different teams/companies (outsourcing?) that used different frameworks
- you could upgrade/migrate to another framework bit by bit instead of rewriting your application in one big step (or instead of never doing it because it's too much work)
- you could write/use small independent modules like Symfony's bundles, except that you wouldn't have to use Symfony to use them

For this to work, you would have to have an entry point that dispatches a request to the correct framework (this sounds like a router). Something like this could be done using [Stack](http://stackphp.com/) and a "router" middleware for example.

{% img /images/posts/container-interop.png %}
