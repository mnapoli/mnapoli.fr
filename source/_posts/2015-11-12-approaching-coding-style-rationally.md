---
layout: post
title: "Approaching coding style rationally"
date: 2015-11-12 18:00
comments: true
categories: best-practices
---

Let's talk about how to format code and name things.

{% img /images/posts/pitchforks.jpg 300 %}

I remember when PSR-1 and PSR-2 became a thing. Jeez that "use only spaces" thing was just ridiculous, I *knew* that tabs where better (right?). And those crazy formatting rules were the opposite of what I was used to. Nonsense! One day I did the jump and after a few weeks, I just didn't care anymore. I was over it, just like everyone else did, and [PSR-2](http://www.php-fig.org/psr/psr-2/) was my new religion.

Fast forward a few years and I'm at a talk where the speaker argues to use [snake case](https://en.wikipedia.org/wiki/Snake_case) for naming test methods. Dude, you crazy? We just finally got consistent style across all the PHP world! And we all know that camelCase *looks much better* right? How can I go against PSR-2? It turns out, after trying it, that this was a wonderful idea…

Habits are sometimes making us blind. We think X looks prettier than Y but that's just the habit speaking. In this article I'll try to take a rational approach at coding style. That means leaving the "it looks ugly/better" at the door.

<!--more-->

If at any point you feel like something "just doesn't look good", breath in, breath out, and **try it!** Nothing beats hands-on experience, not even some random article on the internet :)

## Trailing commas?

Should we use trailing commas or not? If you are not familiar with the practice, here is an array using trailing commas:

```php
$flavors = [
   'chocolate',
   'vanilla',
];
```

As you can see, the last item ends with a comma even though there is no extra item after it. It's perfectly valid PHP, the trailing comma is simply ignored. Now consider this example which is **not** using trailing commas:

```php
$flavors = [
   'chocolate',
   'vanilla'
];
```

Let's add a new value to the array:

```php
$flavors = [
   'chocolate',
   'vanilla',
   'lemon'
];
```

Here is the diff we generated in the commit:

```diff
$flavors = [
   'chocolate',
-   'vanilla'
+   'vanilla',
+   'lemon'
];
```

With trailing commas, the diff looks like this instead:

```diff
$flavors = [
   'chocolate',
   'vanilla',
+   'lemon',
];
```

As you can see, trailing commas lead to simpler and cleaner commits. This is also very useful when using `git blame`: each line points to the real commit that added it. Conclusion: **use trailing commas**.

It's interesting to note that there's currently a RFC to allow trailing commas everywhere in PHP (not just arrays). Obviously, I think it would be a great addition to the language for the reasons explained above, as well as for the sake of consistency.

## Value alignment?

This practice is fairly common, though not universal. PSR-2 doesn't state anything about it, but many projects enforce such rule. Here is an example with phpdoc:

```php
/**
 * @param int             $id       ID of the thing to export.
 * @param string          $filename Name of the file in which to export.
 * @param LoggerInterface $logger   Used to log the progress of the export.
 */
```

And here is another one with arrays:

```php
$formTypes = [
   'text'     => new TextField,
   'select'   => new SelectField,
   'checkbox' => new CheckboxField,
];
```

Or for assignments:

```php
$firstname = 'Spongebob';
$lastName  = 'Squarepants';
$age       = 25;
```

Anyone who has ever modified such code knows: it's a pain. Sure it may "look good", but when modifying that you have to fill in all the extra spaces to keep the alignment. One may argue that an IDE can re-arrange that for us, but even then let's look at the diff when adding a new item to an array:

```diff
$formTypes = [
-   'text'     => new TextField,
-   'select'   => new SelectField,
-   'checkbox' => new CheckboxField,
+   'text'           => new TextField,
+   'select'         => new SelectField,
+   'checkbox'       => new CheckboxField,
+   'my_custom_type' => new CheckboxField,
];
```

Now all the `git blame` information is lost and the commit is almost unreadable.

Alternatively, if you don't bother keeping the alignment:

```php
$formTypes = [
   'text'     => new TextField,
   'select'   => new SelectField,
   'checkbox' => new CheckboxField,
   'my_custom_type' => new CheckboxField,
];
```

The whole point of the alignement is lost, and there goes consistency.

Another example following an IDE refactoring (`LoggerInterface` was renamed to `Logger`):

```php
/**
 * @param int             $id       ID of the thing to export.
 * @param string          $filename Name of the file in which to export.
 * @param Logger $logger   Used to log the progress of the export.
 */
```

We have all seen that!

To sum up on aligning values:

- it requires extra work to maintain
- it messes up diffs and `git blame`
- it leads to inconsistent alignment over time

Conclusion: **do not align things with spaces**.

## Minimal phpdoc

Using phpdoc to document classes, functions, methods, etc. is good practice. However code considered as "well documented" usually looks like this ([on GitHub](https://github.com/symfony/symfony/blob/v2.7.6/src/Symfony/Bundle/FrameworkBundle/HttpCache/HttpCache.php#L31-L37)):

```php
/**
 * Constructor.
 *
 * @param HttpKernelInterface $kernel An HttpKernelInterface instance
 * @param string $cacheDir The cache directory (default used if null)
 */
public function __construct(HttpKernelInterface $kernel, $cacheDir = null)
{ ... }
```

There is much content in this docblock, but most of it is duplicated from what developers or tools can already get from the source:

- we get that it's a constructor, the method is `__construct()`
- we get that `$kernel` is a `HttpKernelInterface`, the parameter is type-hinted
- we get that a `HttpKernelInterface` type-hint means that the parameter must be "An HttpKernelInterface instance" (this comment has no added value)

In reality here is what the docblock provides that isn't provided by the code itself:

- `$cacheDir` is a string (or null)
- if `$cacheDir` is null, the default cache directory will be used

The docblock could be reduced to this:

```php
/**
 * @param string|null $cacheDir The cache directory (default used if null)
 */
public function __construct(HttpKernelInterface $kernel, $cacheDir = null)
{ ... }
```

On top of being information overload, the duplication also becomes a problem when the code changes. Everbody has seen a docblock that doesn't match the method it describes. When information is duplicated this is much more likely to happen.

All of this is becoming even more interesting with PHP 7 (coming with scalar type-hints and return types). Here is [another example](https://github.com/symfony/symfony/blob/v2.7.6/src/Symfony/Component/HttpKernel/KernelInterface.php#L113-L118) to illustrate that:

```php
interface KernelInterface
{
    ...

    /**
     * Gets the name of the kernel.
     *
     * @return string The kernel name
     */
    public function getName();
}
```

With PHP 7 the docblock would become entirely useless:

```php
interface KernelInterface
{
    ...

    public function getName() : string;
}
```

Conclusion: **use docblocks only to add information.**

## The "Interface" suffix

This is a topic that has [already](http://verraes.net/2013/09/sensible-interfaces/) [been](http://phpixie.com/blog/naming-interfaces-in-php/) [debated](https://groups.google.com/forum/#!topic/php-fig/aBUPKfTwyHo) (the first link is the best by the way) so let's get right to the point: **in most cases, there is no need to have `Interface` in the name of an interface**.

Let's take that example:

```php
class Foo
{
    public function __construct(CacheInterface $cache)
    { ... }
}
```

One would argue here that the fact that we ask for an interface *explicitly* (it's visible through the name) is good: we know we ask for an interface. If the type-hint was for `Cache`, we wouldn't be sure whether we are asking for an interface or for an implementation.

My answer to that is that from the consumer's point of view, **it doesn't matter if it's an interface or an implementation**. What matters is that `Foo` needs a cache, end of story. There's a principle behind interfaces: either you have one implementation and you don't need an interface, either you have many implementations and you create an interface.

If for some reason there's only one implementation of the cache, then fine: just give me an instance of the class! If however there are multiples implementation of the cache, then **each implementation exists for a specific reason**. And that should be visible in the name. There is no reason there would be one implementation named `Cache`. There would be `RedisCache`, `FileCache`, `ArrayCache`, etc.

In the end, when we use the name `Cache` we either type-hint against the one implementation (no interface exists), or either against the interface. All is well!

I believe we have issues with this because :

- we are sometimes tempted to create interfaces when it's not needed, just because interfaces sound like bonus points towards clean code (thus leading to `Foo implements FooInterface`)
- it requires coming up with unique names for implementations, and [naming things is hard](http://martinfowler.com/bliki/TwoHardThings.html)

But even though this is challenging our habits, getting rid of `*Interface` forces us to think: better names and no unneeded interfaces.

For example if `UserRepository` is an interface, then you are forced to find a more specific name for your implementation. And you come up with `DoctrineUserRepository` and you realize that there could as well be `EloquentUserRepository`, `PdoUserRepository` or `InMemoryUserRepository`. **Interfaces makes much more sense when they are the default**. Implementations are secondary.

Let's keep in mind however that in some cases (for example in libraries/frameworks), interfaces are introduced only to allow a third party to replace the *default* implementation. We then have an interface with one implementation (other implementations are to be written by framework users). Given that context isn't the same as what was described above, it's harder to apply the same principles. In that case I can only advise to use critical thinking :)

## The "Exception" suffix

What, again the suffixes? Yes!

*this section is inspired from [Kevlin's mind-blowing talk](https://vimeo.com/album/2661665/video/74316116)*

Here is where we use exceptions in PHP:

- `throw ...`
- `catch (...)`
- `@throws` in phpdoc
- when creating an exception class by extending another one

That means that everywhere an exception class will appear, we will know it's an exception. Having the `Exception` suffix is then completely redundant.

Let's take an example: `UserNotFoundException`. The suffix brings absolutely no value. Even worse, it makes the (perfectly valid) sentence more obscure: `UserNotFound` is everything we need.

What's even more interesting is that, in some cases, removing the `Exception` suffix makes the name look quite bad. As an exercise, let's remove the suffix from [Symfony Form's exceptions](https://github.com/symfony/symfony/tree/v2.7.6/src/Symfony/Component/Form/Exception). By the way I'm taking examples from Symfony not because I believe it is bad code, but on the contrary because it is a very good code base (thus making the point stronger).

First let's start with the exceptions that would make perfect sense without the suffix:

- `BadMethodCall`
- `InvalidArgument`
- `InvalidConfiguration`
- `OutOfBounds`
- `TransformationFailed`
- `UnexpectedType`

Those sound like perfectly valid english sentences that explain an error.

Now let's look at those that sound more like an information than an exception or error:

- `AlreadyBound`
- `AlreadySubmitted`

Imagine that situation in real life: you want to get on the bus, but the driver tells you "no, somebody already got on". You would probably ask "So what?". The driver's problem is maybe that the bus is full. It's the same here: maybe a form cannot be re-bound if it has already been bound.

How about `CannotRebind` and `CannotSubmitAgain` as exception names instead?

Lastly, let's look at the exceptions left:

- `ErrorMappingException` (notice the combo error + exception)
- `LogicException`: exception/error in the logic
- `RuntimeException`: exception/error at runtime
- `StringCastException`: exception/error while casting to string?

Except `RuntimeException` and `LogicException` which are very generic exceptions (maybe they deserve to be more specific?), here are suggestions for the other two:

- `InvalidMapping`
- `CannotBeCastToString`

Not convinced? Have a look at your own code and do the same exercise, it might give you a new persepective on your errors.

Conclusion: **the `Exception` suffix is unnecessary**. Not using it also has the benefit of forcing us to come up with names that better describe an actual error.

## Conclusion

In the end we only had a look at 5 actual examples, but I want to stress that the main point of this article is:

- it's possible to think about coding style logically
- sometimes doing so forces us to challenge our habits
- when unsure or dubious: just try

If you need to vent off on how some of this is stupid and ugly, there's a comment box below. I would also be happy to hear about practices you tried and you liked!
