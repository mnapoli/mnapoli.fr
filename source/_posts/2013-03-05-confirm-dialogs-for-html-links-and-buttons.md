---
layout: post
title: "jQuery plugin: Confirm dialogs for HTML links and buttons"
date: 2013-03-05 20:00
comments: true
external-url:
categories: jquery myclabs open-source
---

This is the first open source project created at my company, so I am quite proud of it even though it is not much.

The name is [**jquery.confirm**](http://myclabs.github.io/jquery.confirm/), which is pretty explicit. It lets you have confirmation dialogs for links or buttons. For example, **"Are you sure you want to delete that comment?"** kind of things.

I am going to present its basic usage and options here.

The idea is to write [unobtrusive Javascript](http://en.wikipedia.org/wiki/Unobtrusive_JavaScript) by letting the user write clean HTML:

```html
<a href="home" class="confirm">Go to home</a>
```

To enable confirmation on this link, simply:

```javascript
$(".confirm").confirm();
```

<!-- more -->

That will show a confirmation dialog each time the user clicks the link. If the user confirms, the plugin will then redirect him to the link.

You can configure the texts and labels through the options. You can also change the actions that are executed when the user confirms (follow the link) or cancels (do nothing), so you can perform AJAX requests for example.

One interesting option is to force the link to be called with a POST request instead of a GET:

```javascript
$(".confirm").confirm({
    post: true
});
```

On your server-side code, you can check that the request is POST and refuse GET request. That can help prevent security issues like someone sending a link to delete someone else’s account for example: http://example.com/my-account/delete. If you only accept POST request, people clicking on that link won’t see their account deleted (because the request would be a GET).

If you want to learn more or try it, the website contains the [official documentation and some demos](http://myclabs.github.io/jquery.confirm/).
