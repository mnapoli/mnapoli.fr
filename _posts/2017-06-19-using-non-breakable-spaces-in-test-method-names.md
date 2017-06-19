---
layout: post
title: "Using non-breakable spaces in test method names"
date: 2017-06-19 10:00
comments: true
---

![](/images/posts/nbsp-wat.jpg)

**Yes. This article is about using non-breakable spaces to name tests. And the fact that it's awesome. And why you should use them too.**

```php
public function test a user can add a product to a wishlist()
{
    // ...
}
```

The code above [is valid PHP code](https://3v4l.org/MrqVL) and works. [Non-breaking spaces](https://en.wikipedia.org/wiki/Non-breaking_space) (aka `&nbsp;` in HTML) look like spaces in editors but are actually interpreted like any other character by PHP.

<!--more-->

---

This is the kind of test that we usually see:

```php
public function testAddProductToWishlist()
{
    // ...
}
```

This is also the kind of method names we were writing at [Wizaplace](http://www.wizaplace.com/) a few years ago.

At that time I was very impressed by a couple of talks which got me [interested in coding style](/approaching-coding-style-rationally/). Using `snake_case` instead of `camelCase` started to make sense in test methods because it lets us explain much more clearly what the test does:

```php
public function test_a_user_can_add_a_product_to_a_wishlist()
{
    // ...
}
```

(☝️ this is not PSR-2 compliant, I was dubious but yes, with time it's possible to get over it)

We ended up discussing this kind of naming in the team. Fortunately, this was also the time we were joking about [writing a PHP 6 framework](https://github.com/wizaplace/thephp6framework) and playing with emojis as class or method names (which [definitely works in PHP](https://github.com/fideloper/larvel)).

At some point someone trolled:

> if we decide to not follow PSR-2 naming for test methods because of readability, we might as well use non-breakable spaces since it's even more readable…

That started as a joke but it just made sense. Since logic and humans don't always mix well together, we decided to try a [small controlled experiment](http://verraes.net/2014/03/small-controlled-experiments/) for a while and see if it was actually great in practice.

```php
public function test a user can add a product to a wishlist()
{
    // ...
}
```

And it was great. So great that it has been more than a year now and we are still completely happy with it.

Test methods are clear and meaningful, here is an actual example of a diff of one of our pull request:

```diff
-     public function testProjectMultiVendorProductWithOneDetached()
+     public function test product and multivendor product projections are both updated when they are detached()
      {
```

Since test methods look like sentences, we think of them as sentences and it makes all tests much clearer. Here are a couple more examples:

```php
public function test very long slugs are truncated()
{
    // ...
}

public function test there are no projects by default()
{
    self::assertEmpty($this->projectService->getProjects());
}
```

## FAQ

### How to actually type a non-breaking space?

This is very easy and quickly memorized:

- MacOS: `Alt`+`Space`
- Ubuntu: `Alt Gr`+`Space` (`Alt Gr` is the right `Alt` key)

### Does it work with all the tools?

In our experience yes, all the tools we use below work perfectly fine:

- git
- PhpStorm
    - update: the shortcut will invoke the "Quick definition" helper, you will need to disable (or remap) that shortcut; "Quick definition" is also available via `Cmd+Y` or `Ctrl+Y` by default so remove the shortcut should be enough
- Sublime Text
- GitHub (and formerly Gitlab)
- PHPUnit's integration in PhpStorm (right-click and "Run" still works)
- PhpStorm's analyzer and refactoring tools:

![](/images/posts/nbsp-code.png)

We have seen minor issues on Atom and Visual Studio Code (syntax highlighting was off), those were fixed by [Florent](https://twitter.com/florent_viel) in the following pull requests: [atom/language-php#196](https://github.com/atom/language-php/pull/196) and [Microsoft/vscode#26992](https://github.com/Microsoft/vscode/pull/26992).

### Does it work with all the humans?

This might be the most difficult part: getting other humans on board. Every new colleague that joins the team has that "WTF" moment when reading the code. That goes against the [principle of least astonishment](https://en.wikipedia.org/wiki/Principle_of_least_astonishment), but we are so proud and happy with non-breaking spaces that explaining it is always a fun time :)

In our experience colleagues got on board pretty quickly, both junior and senior developers. Our team is still small though, it might be more difficult to introduce this in a large organization with multiple teams.

### How can you tell if you've typed a classic space by mistake?

Don't worry, you'll see it immediately:

![](/images/posts/nbsp-error.png)

Again, in our experience it was never an issue.

### Can it work in open source projects?

That is the only reservation I have at the moment. Using non-breaking spaces in a closed source project where the team can own the code and share the decisions is easy: if it works, keep doing it, else stop.

In open source projects it's more complex since most contributors will get their WTF moment without you by their side to explain. It may be confusing or even off-putting.

My personal stance for now is:

- use this approach for small projects that will probably get no contributors
- spread that practice as much as possible (this is what this article is about)
- hope that it catches on to be able to use it more and more without
