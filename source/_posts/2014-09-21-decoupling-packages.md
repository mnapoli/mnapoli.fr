---
layout: post
title: "Decoupling packages"
date: 2014-09-21 23:00
comments: true
categories: architecture best-practices open-source
---

Decoupling packages is a hard thing. There are not a lot of options, and this blog post is about how some options are better than others.

Let's say for example that you are writing a "package", or library, to respond to HTTP requests (that kind of package could be considered the basis for a web framework). How do you handle routing?

If you write your Router package as an independent package (which is good: small and specialized packages are more reusable and maintainable), you might not want to couple the HTTP package to the Router package: you want to leave users free to choose the router of their choice.

So, what are your options to make the HTTP package and the Router package decoupled from each other?

<!--more-->

## Events

The first option is to turn to **event-driven programming**. By relying on an "event manager" library/package, you can have your package raise specific events at strategic points in your code. And you can have those events affect the code flow.

This is the solution chosen by Symfony to solve the routing problem exposed earlier. Their HttpKernel component is decoupled form their Routing component through events.

Here is the simplified code flow of their `HttpKernel` class/component (which is the basis for an HTTP application):

```php
$eventDispatcher->dispatch(
    KernelEvents::REQUEST,
    new GetResponseEvent($this, $request, $type)
);

$controller = $request->attributes->get('_controller');

if (! $controller) {
    throw new NotFoundHttpException();
}
```

So here is what's happening:

- the `HttpKernel` raises a "Kernel Request" event
- then **it expects** that a listener set the controller under the "_controller" key in the request

So is `HttpKernel` decoupled from the `Router` component? **Yes**. The listener that sets the controller could actually be anything.

The problem is, as emphasized above, that the `HttpKernel` **expects something very specific from the listeners**. The whole application depends on an unknown listener being actually registered for that particular event and following an exact, **unspecified** behavior.

I believe events should be used for hooking up in the main logic flow to *extend it*. **But the main logic flow should be linear and not rely on the possible *side effects* of an event.**

There are some other problems we can find with this solution:

- the package ends up coupled to the "event manager" package (you just replaced a dependency by another)
- the code is not linear: it makes it much harder for developers to put the pieces back together and contribute to the project
- behavior from decoupled packages **are not specified by contracts**

Regarding the pros:

- the behavior is *not specified*, which can also be a good thing: it leaves every possible option open for the future
- might be simpler than the other options

`<disclaimer>`Just to be clear, Symfony is an exceptional framework and I love it. I am sure the decision to choose this option was carefully thought through, and the important thing to remember is that **it works**. Symfony is probably the most popular modern PHP framework, and my blog post doesn't change that. I am just using it as an example here, I just want to expose alternative options and discuss the pros and cons.`</disclaimer>`

## Interfaces and adapters

I was mentioning *specifying behaviors* with **contracts**. In PHP (and most OO languages), you can implement this using **interfaces**.

If we take the previous example, an HTTP package could contain a `RouterInterface` to specify how a routing component should behave (this is a very basic example):

```php
namespace Acme\Http;

interface RouterInterface {
    /**
     * @return callable The controller to use for this request
     */
    public function route(Request $request);
}
```

You'll notice that not everything is specifiable, e.g. the return types. Hopefully PHP will allow that in its next major version, but until then the only solution is to use documentation.

In the `HttpApplication` class, we can use it in type-hints to accept any implementation (classic dependency injection here). And here is what the code logic would look like:

```php
$controller = $router->route($request);

if (! $controller) {
    throw new NotFoundHttpException();
}
```

**The code flow is linear and completely explicit.** And the HTTP package is decoupled from any Router package!

The only problem left: Router packages would be forced to implement `Acme\Http\RouterInterface` if they want to be used with the HTTP package. Because of that, they end up coupled to it… So how do we decouple Router packages from the HTTP package?

A way to go around that is to use **adapters**:

```php
namespace \MyApplication\Adapter;

class HttpRouterInterfaceAdapter implements \Acme\Http\RouterInterface {
    private $router;

    public function __construct(\Acme\Router\Router $router) {
        $this->router = $router;
    }
    
    public function route(Request $request) {
    	  $this->router->route($request);
    }
}
```

Thanks to that adapter, the `Router` class doesn't need to implement the `RouterInterface`. So it is completely decoupled from the HTTP package.

However, as you can see, it requires writing an adapter each time you want to "connect" decoupled packages.

Pros:

- linear and explicit code flow
- specified behavior (using interfaces)

Cons:

- requires to write interfaces
- requires to write adapters

This strategy of interfaces was recently used by **Laravel**. For the version 5.0 (IIRC), Laravel will publish a package named [`illuminate/contracts`](https://github.com/illuminate/contracts) which contains all the interfaces used by its other packages.

That allows to have decoupled Laravel packages while not needing adapters to use them together: packages can implement the interfaces at the very small cost of being coupled to `illuminate/contracts` (it's a small cost because the package is very light and contains only interfaces).

## Standardized interfaces

Now the last option is to go a step beyond and try to make the interfaces "**standard**". By that I mean that the same interface would be used by many packages, and implemented by many others.

The good example for this is obviously **logging**. There used to be a numerous amount of different logger libraries for PHP. Then the [PHP-FIG](http://www.php-fig.org/) group worked to produce [PSR-3](https://github.com/php-fig/log), the logger standard.

Today, many logging packages implement the `Psr\Log\LoggerInterface`, and most modern frameworks type-hint against that interface instead of specific implementations. That means that users can choose any PSR-3 compliant logger and have their framework use it.

Needless to say that this is an ideal situation: **no coupling, no effort**! But logging was kind of an easy topic. It's very hard to come up with standards for all the other components that need interfaces, mainly because implementations often differ a lot.

The [PHP-FIG](http://www.php-fig.org/) has been working for a few years on a Cache and an HTTP message PSR, and hopefully they will be released sometime. In the meantime, the [container-interop project](https://github.com/container-interop/container-interop) aims at providing interfaces to standardize the usage of dependency injection containers.

## Conclusion

OK, there's not much left to add here, if you have any reaction about this I'd be happy to hear it. If I got anything wrong, I'd be happy to correct it.

I would like to finish on an idea that was suggested about a year ago on the PHP internals mailing list: "**weak interfaces**". Those are interfaces that define a behavior, but **that do not need to be implemented by classes**. It mixes the principle of static-typing with duck-typing:

> If it looks like a duck and quacks like a duck, then it's a duck.

What's really good with this is that it allows packages to define their interfaces, and type-hint against it, all the while not requiring dependencies to actually implement it. As long as objects are compatible with the interface, it works. It's a sort of `class X implements Y` evaluated at runtime. Example:

```php
interface FooInterface {
    public function hello();
}

// Foo does not implement FooInterface
class Foo {
    // But this method makes it compatible with FooInterface
    public function hello()
    {
        return 'Hello world';
    }
}

// That pseudo-syntax tells that this is a weak-interface type-hinting
function run(<<FooInterface>> $foo) {
	echo $foo;
}

// It works because Foo is compatible with the interface
run(new Foo);

// Error, stdClass is not compatible with FooInterface
run(new stdClass);
```

This example was just for fun, but I wish such a feature would land in PHP (along with static return type). It would help a lot with package interoperability and decoupling.