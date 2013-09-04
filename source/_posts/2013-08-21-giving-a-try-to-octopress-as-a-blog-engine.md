---
layout: post
title: "Giving a try to Octopress as a blog engine"
date: 2013-08-21 16:17
comments: true
categories:
---

This blog was running on Wordpress. And I hated it.

Writing an article was really not funny, especially when I needed to include some code!

In 2013, what's the best way to write down text and code? **Markdown**!

<!-- more -->

So I gave a try to [Jekyll](http://jekyllrb.com/) first, and it was quite nice. But then I stumbled upon [**Octopress**](http://octopress.org/), and given that is a real blog engine, I switched. I had to rewrite all the previous article into Markdown, and it was fast!

So right now I'm loving it. Especially the fact that I can write drafts anywhere using a text editor, that anybody can submit pull requests through Github to fix an article, and that my articles are finally in a standardized reusable format on my hard drive.

It's fair to mention the not funny parts though:

- You need to install ruby, rvm, and many things. Because of OS X, zsh and stuff, I got stuck and frustrated :(
- With Octopress, you need to work on a `source` branch, not the `master` branch: there's a `rake deploy` command to publish to master
- It's not that easy to work with themes, plugins, …
