---
layout: post
title: "Silly CLI 1.1 comes with dependency injection"
date: 2015-04-12 18:00
comments: true
categories: silly container-interop
---

I have just released [Silly CLI](http://mnapoli.fr/silly/) 1.1 and I think it's awesome, here's why.

## What is Silly?

If you missed it, [Silly](http://mnapoli.fr/silly/) is a small project I started a month ago. The idea is simple: *it is a CLI micro-framework*.

Much like Silex or Slim let you create a web application in a single file using closures as controllers, Silly let you create a CLI application in a single file using closures as commands:

```php
$app = new Silly\Application();

$app->command('greet [name] [--yell]', function ($name, $yell, $output) {
    $text = $name ? 'Hello '.$name : 'Hello';
    
    if ($yell) {
        $text = strtoupper($text);
    }

    $output->writeln($text);
});

$app->run();
```

<!-- more -->

As you can see, no need for classes. Also, commands are defined using a syntax inspired from help and man pages which is short and reads nicely.

Running the application:

```
$ php app.php greet
Hello
$ php app.php greet john --yell
HELLO JOHN
```

Of course I didn't reinvent anything. All this is based on [Symfony Console](http://symfony.com/fr/doc/current/components/console/introduction.html), which is great because:

- I'm lazy
- everybody knows the Symfony Console

This was Silly CLI 1.0, and it was good.

## Scaling up

I like micro-frameworks because they allow us to start very simply and very quickly. They also generally don't force us into a specific architecture or organization which, sometimes, is good.

But the value of a micro-framework is even greater when it allows you to **scale up**. By that I don't mean "mongodb web-scale", I mean "my single script file is too big, I want to organize everything more properly and start doing complex stuff".

### Callables

This is where a immensely useful concept of PHP comes into play: **callables**.

Most examples of micro-frameworks show you the "closure" example of callables because it's the most obvious one for a simple app:

```php
$app->command('greet [name]', function ($name) {
    // ...
});
```

But as you probably know, closures are not the only kind of *callables* in PHP. Here is a list of valid PHP callables:

- a closure: `function () {}`
- a function name: `'strlen'`
- an object method: `[$object, 'theMethod']`
- a class static method: `['TheClass', 'theMethod']`
- invokable object: `$object` (an object that has an `__invoke()` method)

All that means is we can replace our closure with classes. Our simple example can then become:

```php
class GreetCommand
{
    public function execute($name)
    {
        // ...
    }
}

$app->command('greet [name]', [new GreetCommand, 'execute']);
```

That's a nice first step to organize our growing application. Each command can be put in its own class and file.

## Silly 1.1: Dependency injection

Putting our commands in separate files is a good start. But everyone knows that a growing application is not just about organizing code: it's also about managing dependencies.

Instead of turning towards singletons or global functions/static methods, Silly 1.1 helps you into using dependency injection. More specifically, it comes with **dependency injection container support**.

### Choose your container

Silex, Slim, Cilex, … all force you into using a specific container (often Pimple). While this is practical at first, it's usually not so good for *scaling up*: the more classes or dependencies you have, the more configuration you will write.

Instead of forcing you into using a *supposedly* better DI container, Silly goes [the interoperability route](https://github.com/container-interop/container-interop): **use whichever container you want!**

```php
$app->useContainer($container);
```

The container you provide has to be compliant with the [container-interop](https://github.com/container-interop/container-interop) standard. If it isn't you can use [Acclimate](https://github.com/jeremeamia/acclimate-container).

### Callables in the container

By simply registering a container with `->useContainer()`, Silly will retrieve commands from the container when they are not PHP callables:

```php
// Valid PHP callables: doesn't use the container
$app->command('greet [name]', function () { /* ... */ });
$app->command('greet [name]', [new GreetCommand, 'execute']);
$app->command('greet [name]', ['SomeClass', 'someStaticMethod']);
$app->command('greet [name]', new InvokableClassCommand);

// Use the container
$app->command('greet [name]', ['GreetCommand', 'execute']); // execute is not a static method
$app->command('greet [name]', 'InvokableClassCommand');     // implements __invoke()
```

In the 2 last examples above, the container will be called to instantiate `GreetCommand` and `InvokableClassCommand`.

That means that you can configure those classes in your container and benefit from the dependency injection features of your container. For example:

```php
class GreetCommand
{
    private $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute($name)
    {
        // ...
    }
}
```

Note that our class could also be an invokable class (i.e. one that [implements `__invoke()`](http://php.net/manual/en/language.oop5.magic.php#object.invoke)).

### Dependency injection in parameters

Dependency injection as shown above only works when you write commands in classes. However you can't use it with closures!

To solve that problem, let's have a look at how dependency injection is performed in AngularJS:

```js
angular.controller('MyController', function (myService, myOtherService) {
  // ...
});
```

Silly supports the same mechanism of dependency injection through the callable parameters. For example:

```php
use Psr\Logger\LoggerInterface;

// the order of parameters doesn't matter:
$app->command('process [directory]', function (LoggerInterface $logger, $directory) {
    $logger->info('Processing directory ' . $directory);

    // ...
});
```

Silly can inject services and values into parameters by looking into the container using:

- the **type-hint** (i.e. the interface/class name): `Psr\Logger\LoggerInterface`
- the **parameter's name**: `logger`

Depending on how you declare your container entries you might want to enable one or the other way, or both.

```php
$app->useContainer($container, $injectByTypeHint = true, $injectByParameterName = true);
```

If you set both to `true`, it will first look using the type-hint, then using the parameter name.

## The PHP-DI and Pimple edition

Being able to use any container is nice, but sometimes you don't really care and just want to get started. You are covered!

I have created two packages that provide you Silly pre-configured with either [PHP-DI](http://php-di.org) or [Pimple](http://pimple.sensiolabs.org/).

- read more about [the PHP-DI edition](http://mnapoli.fr/silly/docs/php-di.html)
- read more about [the Pimple edition](http://mnapoli.fr/silly/docs/pimple.html)

Those wanting the benefit of autowiring might like the former, while those familiar with Silex might feel more at ease with the latter.

## Beyond Silly

I hope this example will show how a framework can be built without coupling to a dependency injection container. This was rather easy because it is a micro-framework, but I hope it will give others some ideas.

For those interested to learn more about these topics, everything explained here was actually implemented in a library called [Invoker](https://github.com/mnapoli/Invoker). Feel free to have a look at it and maybe use it in your projects. The same principles shown here could be applied to any framework dispatching to PHP callables. Hopefully it will catch on!

*Thanks for [@Darhazer](https://twitter.com/Darhazer) and [@najidev](https://twitter.com/najidev) for reviewing this article.*
