---
layout: post
title: "A retrospective on open sourcing projects at My C-Sense"
date: 2014-07-01 18:00
comments: true
categories: open-source myclabs
---

Today is my last day at [**My C-Sense**](http://www.myc-sense.com), after almost 2 years working on creating and modernizing existing PHP applications. This has been an incredibly rich experience in an excellent work environment, I will surely miss this job and my colleagues.

Now is probably a good time to look back on our open source experience as a company, and as a developer. It all started when we moved all of our code to GitHub: we used a lot of open source software and libraries, and we thought we might try to contribute some day with some small internal projects too.

So that's how [**MyCLabs**](https://github.com/myclabs) was born: let's keep the door open for open source because "why not".

<!-- more -->

## jquery.confirm

The first project was born out of a simple need: a confirmation box on some buttons and links. We were using Bootstrap, but adding all the JS and HTML needed for such a simple thing was definitely worth a JS function. So instead of a quick and dirty solution, I turned to jQuery and learned how plugins worked, just for fun and because it wasn't a big investment. In no time, [**jquery.confirm**](https://github.com/myclabs/jquery.confirm) was born (the [demo is here](http://myclabs.github.io/jquery.confirm)).

The project was absolutely not ambitious, yet it's probably our most successful project yet. It has *30 forks*, received 10 pull requests and has 13 releases. That's not much, but for a simple confirmation dialog, it's quite nice.

And what's interesting is that it's the perfect example of an open source "success" from the company's point of view: we receive much more contributions than we contribute to it ourselves. We get bugfixes "for free", and we even got our migration to Bootstrap 3 covered for us [by a contributor](https://github.com/myclabs/jquery.confirm/pull/10). Which company wouldn't want that?

## PHP Enum

The next project open sourced was a bit different. [**PHP Enum**](https://github.com/myclabs/php-enum) is an implementation of an Enum structure for PHP. That's probably not the first time you hear about something like that, so here is why we created one:

- [SplEnum](http://php.net/manual/en/class.splenum.php) seems like a good solution, but it is actually a separate PHP extension that needs to be installed (which makes it useless IMO)
- the other open source implementations were not good enough: either they were too far from SplEnum (which means most of the time they were working in a weird way), either they were a direct clone of SplEnum and there was a bit of room for improvement. If you do a search today, you'll find other good libraries that were different or non-existant at the time

We ended up using it in some places in our application, and I ended up using it in [PHP-DI](http://php-di.org). We received a few contributions, but obviously there's not a lot of room for bugs or new features since it's very simple.

In the end, this library has been installed *more than 16 000 times*, which is definitely a lot and that makes us happy (PHP-DI installs might represent a good share of that).

## MyCLabs\ACL

[**MyCLabs\ACL**](http://myclabs.github.io/ACL/) is the one I'm the most proud of. We have been working on *the ACL problem* and **access control** for more than 4 years (I've been on and off at My C-Sense), changing, improving and rewriting our solution about every year. It has been a major headache, especially since it's a problem that leaks everywhere: in the model, at the database level, at the routing level, in the views, … And its impact on performance was definitely one of the biggest challenge.

We were always looking at all the existing PHP solutions, but none of them was ever *even close* to fit our need. We've always been amazed about this, because we are sure many other companies have the same needs as us.

So because of all this, our ACL system was something I definitely wanted to open source some day. But given its complexity, and the fact it was deeply coupled to our application, it was impossible. Then came Doctrine 2.4, and then 2.5, and it changed a lot of things. As the next big rewrite (again) was planned, I suggested we tried to make it open source. And that would not mean "just publish the code online". That would mean make the effort on making a completely decoupled library with tests and documentation.

It took a few weeks to build it, and the company paid for that effort. But it was greatly rewarded with a solution much more solid and powerful than we ever built.

**Thinking and building our code on in a more generic way was definitely beneficial.** We previously had an unstable, half-tested, undocumented, slow and unmaintainable ACL system. We wouldn't dare touch it in fear of breaking everything. We now have a very powerful, tested, fast and documented solution. Yes it took time, but it was definitely worth it.

And I think this library is a perfect example of how **open source was beneficial, even without any external contribution**.

And as a developer, making the library open source turned the *most boring problem on earth* (permissions!) into a fascinating one! I couldn't stop thinking about it out of work when I was working on it.

My C-Sense is now working on a [v2](https://github.com/myclabs/ACL/pull/9), with a much more simpler configuration and usage (much less boilerplate code). That's so interesting that I'll probably be following the project and maybe helping out if I have the time ;)

## MyCLabs\Work

The ACL project had been a success, so when we ended up moving away from Gearman and thus rewriting our worker system (coupling, coupling…), I suggested we tried building an open source solution for it. Here's [**MyCLabs\Work**](http://myclabs.github.io/Work/).

Of course, I had a look at everything that could fit the bill. But again, we needed a feature that wasn't provided by any open source library I could find:

> That task is too long, it needs to run in background and the user will be notified by email when it finishes. But sometimes it can be very fast, so receiving an email is useless and looks stupid.
>
> So we want to run the task in background, but if it takes less than 5 seconds, we need to show the result to the user directly (in the web page) as if it didn't run in background. And of course, the worker shouldn't send any email!

That looks like a very simple need from a user's point of view. But when you realize that it means you must have a **bidirectional communication** between the web page and the background worker, you start going "oh no no no".

Now we just ditched Gearman (because of too many bugs and installation problems), and we didn't want to tie ourselves again to a different work queue. So our library was built against 2 needs:

- an abstraction over different queue systems (RabbitMQ, Beanstalkd, InMemory…)
- an abstraction over the "run and wait if it finishes else …"

Of course, the `run and wait` wasn't possible on every queue system, so it needed to be something optional. So I ended up writing 2 interfaces:

```php
interface WorkDispatcher
{
    public function run(Task $task);
}

interface SynchronousWorkDispatcher extends WorkDispatcher
{
    public function runAndWait(
        Task $task,
        $wait = 0, // time to wait, in seconds
        callable $completed = null,
        callable $timedout = null,
        callable $errored = null
    );
}
```

(the details are documented [here](http://myclabs.github.io/Work/))

For example, the Beanstalkd adapter doesn't implement `SynchronousWorkDispatcher`, whereas the RabbitMQ adapter does.

Now where it begins to be interesting is that there is no queue system that provides out of the box bidirectional communication with a worker (at least in the one I reviewed at that time, the one with a good PHP lib and the one that can be installed). After several days of thinking and trials, I managed to implement the `runAndWait` behavior in RabbitMQ with the use of a temp queues and some sort of high level "protocol". Maybe not the fastest solution on earth, but for our needs it wasn't a problem at all. If you are curious, you can checkout [here](https://github.com/myclabs/Work/tree/master/src/Adapter/RabbitMQ) how it is implemented.

In the end, this is really was this library does: it **abstract** 2 behaviors: simply run a task in background, and run a task and wait for its result.

MyCLabs\Work now has adapters for RabbitMQ, Beanstalkd and "In Memory" (i.e. for your development machine), you are welcome to push new adapters through pull requests. It's very simple if you don't implement the `SynchronousWorkDispatcher` interface, for example [look at the Beanstalkd adapter](https://github.com/myclabs/Work/tree/master/src/Adapter/Beanstalkd).

## DeepCopy

[**DeepCopy**](https://github.com/myclabs/DeepCopy) is a small utility that helps you create deep copies of objects. By deep copy, I mean when you want to duplicate/clone an object *and it's sub-objects*.

Imagine you need to provide the user a way to "duplicate a folder". The user will expect all the files and subfolders to be duplicated too. PHP's `clone` will do a shallow copy, which means it will only clone the root object, the properties of the cloned object will reference the same objects as the original object.

The "built-in" PHP solution would be to override `__clone()`, but that leads to quite complex code, and handling cycling dependencies is very hard.

DeepCopy will handle all that for you. And on top of it, it's completely configurable: you can skip properties, force them to keep their original value, or set them to `null`. DeepCopy also supports resolving Doctrine's collections and proxies.

## ArrayComparator

[**ArrayComparator**](https://github.com/myclabs/ArrayComparator) is a bit like DeepCopy: it's a small utility for comparing arrays containing objects. It let's you define callbacks that will be called when items are different or missing between arrays.

## Contributions to other open source projects

While we use the [MyCLabs](https://github.com/myclabs) organization to publish open source libraries, we also use it to host forks of other libraries. That had become quite handy when we proposed a bugfix for Doctrine: let's install our fork instead of the original repo. By the way, [here is how to do it with Composer](http://mnapoli.fr/overriding-dependencies-with-composer/).

So up to now, we contributed mostly to [Doctrine](http://doctrine-project.org), the [DoctrineExtensions](https://github.com/Atlantic18/DoctrineExtensions) and [PHPExcel](https://github.com/PHPOffice/PHPExcel).

## A word on documentation

*This part is a bit of self-promotion :)*

We ended up having 2 kinds of projects:

- small projects where the GitHub project and a single Readme would be enough
- larger projects with multi-pages documentation

For larger projects, we have been using [**Couscous**](http://mnapoli.fr/Couscous/) to generate the websites from the Markdown documentation.

It is working pretty well, the local preview is useful and deploying is very simple. And with the newly implemented templates it proved to be even more useful: I have written a [Couscous template for MyCLabs projects](https://github.com/myclabs/couscous-template), and I use it for [MyCLabs\ACL](http://myclabs.github.io/ACL/) and MyCLabs\Work.

If you too want to use GitHub Pages to publish a website based on your documentation, give it a try.

## Conclusion

This was a big list of most of My C-Sense's open source projects. There are even some more that I didn't mention (because the article is getting quite long), so check out [MyCLabs page](https://github.com/myclabs).

To conclude on the open source experience *from the company's point of view* (and this is my own opinion here), I can only find it beneficial.

Of course, don't expect to get contributions right away and have your software developed for you. Out of all the project we open sourced, we only got a dozen of pull requests. And you have to realize that it takes time to manage them, and the issues (which sometimes are just people asking for help). Remember than an open source project not maintained is hurting the community rather than helping it.

That being said, there are many advantages:

- an open source library usually mean **better quality**, because that code is public. I would much more restrain from committing a dirty hack if I know the code will be online for years :p But mainly it really help the developer to take a step back and really think about what the library is supposed to do in a more generic way, and also to be more open to other implementations.
- a company involved in open source projects looks sexy to developers. **Recruiting** is (I presume) easier, and also it allows to attract and recruit good developers: they know what kind of code and quality level to expect, just like the recruiter does when he browses GitHub profiles of candidates.
- open sourced code get fixes and improvements… sometimes. And for free… almost (if you consider the time needed to merge/handle the tickets)
- **developers do a better work**: publishing an open source project online, or contributing to one, is a great experience. You contribute for something more timeless than your job. The code you write might be used by your peers across the world for year! That's challenging and exciting.

The last point is also about the developer's point of view: working on open source projects is an additional source of motivation and involvement. And of course it benefits everyone.

So, companies: *as long as you don't make money over it, think about open sourcing it?*

Now I will finish with a very warm thank you to My C-Sense and its founders for trusting us and letting us try. Open source has had a very positive result up to now.
