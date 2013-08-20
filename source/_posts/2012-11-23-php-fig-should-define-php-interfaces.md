---
layout: post
title: "The PHP-FIG should define PHP interfaces"
date: 2012-11-23 20:00
comments: true
external-url:
categories: php php-fig
---

Bouncing on the discussion initiated in the [#52](https://github.com/php-fig/fig-standards/issues/57) ticket of the PHP-FIG project on Github: « **Explain the scope of the PSR system** », I’ll explain the case I’m trying to make.

First, [**PHP-FIG**](http://www.php-fig.org/), which stands for *Framework Interoperability Group*, is a gathering of major PHP frameworks and project who try to:

> talk about the commonalities between our projects and find ways we can work together.

This group has released PSR-0, PSR-1 and PSR-2, three specifications of coding standards, guide style and code organisation (for autoloading interoperability). Now the question is asked: is it the role of the PHP-FIG to define technical “code” specifications or is it out of its scope? Here is my answer.

**PSR-0/1-2 are contracts between its users to ensure cohesiveness and compatibility.**

<!-- more -->

Think of the PSR-0 for example, it enabled all projects to be compatible regarding class autoloading. To achieve this, no code or PHP interface was necessary because what the autoloading needed was only file names, directories and class names constraints.

Now there are other questions that need standardization for interoperability between PHP projects. And some of them **need** PHP interfaces.

For example, PHP (or the SPL) [does not define a Collection interface](https://github.com/php-fig/fig-standards/issues/59) (or any implementation). However, a Collection is a base object, and I bet it is used (or could be used) in many projects. Now Doctrine defined their own Collection interface (because it needed it) and I’m sure other projects did the same for the same reasons, but that situation is stupid. A Collection is a standard data structure, implementations may vary but the Collection interface should be defined once and for all.

And **PHP interfaces are contracts between its users to ensure cohesiveness and compatibility**.

Notice any similarity between PSR-0/1/2 and interfaces? They are the same thing, applied to different things. They are technical specifications.

I agree that the SPL was a good start and maybe would have been a good place for such things, but it is a still project, with no big changes lately, a lot of inertia and several big lacks (and who decides what’s in the SPL?). The PHP FIG is the perfect group to bring a solution to this: it is active, dynamic, open and transparent, representative of the major PHP projects, and it has the competences and the momentum to make it useful and used (that will not be “yet another PHP library”, it will be used by major frameworks).

If PHP-FIG doesn’t do it, then who will (and more importantly: who will make it a success)?

And to extend my point, have a look on the Java side (JSR), and for example [JSR-107](http://jcp.org/aboutJava/communityprocess/jsr/cacheFS.pdf) which defines interfaces for cache API, or [JSR-220](http://en.wikipedia.org/wiki/Java_Persistence_API) which defines JPA (specification of persistence API that Doctrine 2 has followed).

**TL/DR**: I think **PHP-FIG should define and provide PHP interfaces**. PHP-FIG defines technical specifications for interoperability between PHP projects. PHP interfaces are a form of technical specifications, they can allow PHP projects to be more compatible and work better together. PHP-FIG is the best group possible to standardize classic/mainstream API (utility classes, …). Java does it, it works, that should inspire us.
