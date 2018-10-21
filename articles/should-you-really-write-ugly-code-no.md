---
layout: post
title: "Should you really write ugly code? Spoiler: no"
date: 2013-06-09 20:00
comments: true
external-url:
categories: best-practices
---

I recently stumbled upon François Zaninotto’s blog post: [You should write ugly code](http://redotheweb.com/2013/06/04/you-should-write-ugly-code.html). While he makes several good points, I strongly disagree with him and I feel the urge to give my opinion on the matter (what did you expect, this is a blog).

The point that he makes is that he encourages developers to write “ugly code”, i.e. code that works and that doesn’t follow best practices or anything related to code quality.

> [..] developers shouldn’t care about code beauty, because that’s not their job. Instead, they should focus on creating great products, which is infinitely more satisfying.

Later, he insists that by writing ugly code, we ship faster than if we had to worry and handle code quality. And shipping faster is good for the business.

Well that’s true. That’s called **Technical Debt**. And like any debt, you’ll have to repay later.

![](/images/posts/technical-debt.png)

<!-- more -->

*Technical debt diagram, borrowed from Planet Geek for the (awesome) “[Clean Code cheat sheet](http://www.planetgeek.ch/2013/06/05/clean-code-cheat-sheet/)“.*

Payback is a bitch and future-you will be cursing present-you for writing ugly code. The cost of change is directly affected, and when you tell your boss that Feature B will takes 2 weeks, he will point out that Feature A took only 2 days.

I won’t get radical as well and say that you should alway write “beautiful code”. What I advocate for is to carefully choose your side.

You need to get a prototype working for a demo? Then get it done quick and dirty! Heck I even used [mysql_query](http://php.net/manual/fr/function.mysql-query.php) on an internal website once, and it worked. I know that this website will never need any real maintenance, so I can sleep without fearing for [violent psychopaths](http://www.codinghorror.com/blog/2008/06/coding-for-violent-psychopaths.html).

But if you plan on writing software that will be maintained for a few years, you would definitely earn by writing beautiful code. Code quality earns you money, and that’s something difficult for managers to get (especially if they have never developed, or never seen code quality benefits before). I once took part on the rewrite of a whole application. After the rewrite, the manager was completely amazed that we needed only a few days to do what would systematically take a month or two before.

I’d like to finish on his take about “*coding trends*” and what he calls “*trend setters*” like Eric Evans with his [Domain Driven Design](http://en.wikipedia.org/wiki/Domain-driven_design). DDD is not a trend, it is a set of patterns and methods to help you in specific situations. [Evans book](http://www.amazon.com/dp/0321125215/ref=cm_sw_su_dp) is very clear about it: DDD can be unhelpful if you try to use it everywhere. That is very reductive to say that all these kind of people (Evans, Martin Fowler, Jeff Atwood) do is “*to explain why pattern A is better than pattern B. Until someone else publishes a book, explaining that pattern C is much, much better.*”

Thanks to these guys, and best practices in general, I make my company earn money with apps that don’t need to be rewritten every year. I can work on my colleague’s code without pulling my hair and wishing him a painful death. I can participate to Open Source projects because I can understand the code.

**TL;DR**

- Go quick and dirty, but expect troubles on the long run
- Care about quality, that will slow you down but get you farther

Both are valid options, but choose carefully.
