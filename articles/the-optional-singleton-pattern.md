---
layout: post
title: "The “Optional Singleton” pattern"
date: 2013-03-22 20:00
comments: true
external-url:
categories: php design-patterns
---

The [singleton](http://en.wikipedia.org/wiki/Singleton_pattern) is a practical design pattern, that’s the reason it is so popular amongst beginners. It is also an anti-pattern because of the problems it introduces (global state, difficult to test, …).

While I agree with that, and the fact that Singletons should be used with (a lot of) moderation, I also like an alternative pattern which comes with the advantage of the singleton and balances out its disadvantages. This can be useful if you have to work on a codebase that has singletons.

I’m calling this pattern the **Optional Singleton** for lack of a better name.

Simply put, this is a class which you can use as a singleton, or not (it’s optional ;):

- you can still use the handy `MySingleton::getInstance()`
- you can however create new instances of the class, for example for tests

<!-- more -->

There is nothing revolutionary about it, see for yourself:

<script src="https://gist.github.com/mnapoli/5221664.js"></script>

Of course, this is a pattern that has to be used where it makes sense. Singletons, as cool as they can be, will never do better than dependency injection.

---

**Update**: I’ve received numerous responses (mostly “the singleton is an anti-pattern” which I agree to). Here is one of my response that I’d like to have here as well:

> The entire point of the singleton pattern is that you **can’t** instantiate the class. That’s why the pattern is called singleton.

My answer:

> Yes, but in 90% of its derived usage it’s not because we want only one instance, it’s because it’s practical.

> Quote from wikipedia: “There is criticism of the use of the singleton pattern, as some consider it an anti-pattern, judging that it is overused, **introduces unnecessary restrictions in situations where a sole instance of a class is not actually required**, and introduces global state into an application.”

> For example one may use the singleton pattern for services: accessing them is practical, you can access them anywhere with the singleton pattern. I’ve seen codebases with this pattern.

> Now if I come on a codebase using the singleton for services, and if I can’t rewrite everything, I’ll turn the Singletons into “Optional Singletons” so that the existing code still work, and so that I can use Dependency Injection over those services in the new code that I’ll write.
