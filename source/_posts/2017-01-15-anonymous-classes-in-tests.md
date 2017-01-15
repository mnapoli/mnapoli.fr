---
layout: post
title: "Using anonymous classes to write simpler tests"
date: 2017-01-15 18:00
comments: true
categories: php tests
---

[Anonymous classes](https://secure.php.net/manual/en/language.oop5.anonymous.php) were added to PHP 7. This article intends to show how useful they can be for writing tests.

<!--more-->

## Mocks

Imagine the following interface:

```php
interface UrlGenerator
{
    public function generateUrl($route);
}
```

If you are testing a class that depends on a `UrlGenerator` implementation, you can write an anonymous class to implement this on the fly:

```php
class MyTest extends TestCase
{
    public function testSomething()
    {
        $urlGenerator = new class() implements UrlGenerator {
            public function generateUrl($route)
            {
                return '';
            }
        };

        $foo = new Foo($urlGenerator);

        // ...
    }
}
```

In the example above we have written a very dumb implementation. That's often useful when the mock will not be used or its behavior isn't important for the use case we are testing.

## Spies

If we wanted to *spy* on invocations of the mock, it's trivial as well:

```php
    public function test()
    {
        $urlGenerator = new class() implements UrlGenerator {
            public $routeToGenerate;
            public function generateUrl($route)
            {
                $this->routeToGenerate = $route;
                return '';
            }
        };

        $foo = new Foo($urlGenerator);
        // ...
        
        self::assertEquals('abcd', $urlGenerator->routeToGenerate);
    }
```

Depending on the scenario, writing mocks or spies using anonymous classes can be a bit more verbose than using a mocking framework (like PHPUnit, PhpSpec, …), so it's not a silver bullet. However it's worth keeping in mind that vanilla PHP code is often easier to understand and debug than a mocking framework.

## Fixture classes

Sometimes when writing tests, you need fixture data. But in some cases you need fixture classes. That can happen when testing tools that use reflection (I've faced that a lot with [PHP-DI](http://php-di.org/) for example).

Writing 5-10 fixture classes is verbose, boring and more importantly it makes tests very hard to read: you have to jump between separate files to retrieve and understand the fixture classes.

Here is an example of a test relying on fixture classes:

```php
class SomeDependencyInjectionContainerTest extends TestCase
{
    public function testSomething()
    {
        $container = new Container();
    
        $obj = $container->get(Class1::class);

        self::assertInstanceOf(Class1::class, $obj);
        self::assertNotInstanceOf(Class2::class, $obj);
    }
}

class Class1 {
}

class Class2 {
}
```

`Class1` and `Class2` and "fixture classes", i.e. those are classes written only for tests. You can put them inside the file containing the test class (but it's not PSR-4 compliant and it leads to conflicts if you have similar classes in other files of the same namespace), or in separate files (but then it's very hard to keep a track of which tests uses which fixture class).

Here is an alternative written using anonymous classes:

```php
class SomeDependencyInjectionContainerTest extends TestCase
{
    public function testSomething()
    {
        // We create 2 fake classes on the fly
        $class1 = get_class(new class() {});
        $class2 = get_class(new class() {});

        $container = new Container();
    
        $obj = $container->get($class1);

        self::assertInstanceOf($class1, $obj);
        self::assertNotInstanceOf($class2, $obj);
    }
}
```

Don't let `get_class(new class() {});` confuse you, it simply allows us to declare a new class and get its name. It's the same as doing this:

```php
class Abcd {
}
$obj = new Abcd();
$className = get_class($obj);
```

With this solution fixture classes are declared inside the test, which means you only need to read the test method to understand the test entirely.

The obvious downside is that you need to understand the concept of anonymous classes, which isn't very widespread today in PHP. But like all new language feature, with time it should be more and more familiar to everyone.
