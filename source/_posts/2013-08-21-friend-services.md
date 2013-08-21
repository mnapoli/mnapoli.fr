---
layout: post
title: "Friend services?"
date: 2013-08-09 17:03
comments: true
categories: php ddd
---

Using the Domain Driven Design methodology, I sometimes end up on such a situation: a behavior in a model class is complex and involving other classes. It’s time to put that behavior in a service (or a factory if the situation applies to a constructor).

The problem is that in my service, I am not in the context of the domain class. If I move `Foo::something()` into `FooService::something($foo)`, then **I can’t access private properties of Foo**, thus limiting me to the public API of Foo.

Now I end up adding accessors, thus breaking encapsulation and complexifying everything where all I wanted was improving the code.

VB.Net has a concept of “Friend” visibility, i.e. if A is friend with B, then A can access private properties of B (or something like that it’s been a long time :p). PHP doesn’t have such a concept natively, but here is a tryout to apply it with workarounds.

<!-- more -->

*Disclaimer*: the code is not pretty and is not for production. This is just an idea thrown around.

```php
<?php
class Foo {
    private $bar = 'hello world';
}
 
class FriendOfFoo {
    public function doSomething($foo) {
        return function() use ($foo) {
            echo $foo->bar;
        };
    }
}
 
$foo = new Foo();
 
$service = new FriendOfFoo();
 
$closure = $service->doSomething($foo)->bindTo(null, $foo);
 
$closure();
```

See it in action on [3v4l.org](http://3v4l.org/e9RNO).

Here, FriendOfFoo is a service that has access to Foo’s private and protected properties.

We achieve that by writing the methods of the service into closures. We can then [bind those closures to the context of Foo](http://php.net/manual/en/closure.bindto.php), and voilà!

If you see a better way of achieving this, I am interested.
