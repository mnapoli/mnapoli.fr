---
layout: post
title: "Be a better programmer: take a step back"
date: 2013-02-04 20:00
comments: true
external-url:
categories: best-practices
---

*Replace [client] by [boss] or anything of the kind if you prefer.*

## A day at work

> **Bug #3890 from Client**
>
> There is an application crash, it says “division by zero error in SpeedCalculator::compute()”.
>
> Please fix ASAP!

<!-- more -->

You open `SpeedCalculator.php` to find:

```php
public function compute() {
    return $this->distance / $this->time;
}
```

## Fixing the bug

Easy! Who wrote that code anyway, how could he not think of that!

```php
public function compute() {
    if ($this->time == 0) {
        return 0;
    }
    return $this->distance / $this->time;
}
```

There you go, the bug is fixed in 2 minutes.

Later, the same bug is found in `RatioCalculator` and `MoneyCalculator`, but once these are fixed too, everyone in the team is sure the problem won’t appear anywhere, it’s gone, for sure this time! The code is rock solid now!

A month later, another bug pops in. The application does not crash anymore, but the client happens to see wrong calculation results in his reports because of the `return 0;`.

## Take a step back

What if, instead of rushing, we took a step back.

> Why did this situation happen?
>
> `$this->time` was set to 0.

Easy! Let’s prevent that.

```php
public function setTime($time) {
    if ($time == 0) {
        throw new InvalidArgumentException("Invalid value");
    }
    $this->time = $time;
}
```

Now this is better, you guarantee the integrity of the data. But the client is not very happy! When he fills the form with a time of 0, the application shows an error page.

So you work on displaying a nice error message by catching the exception in the controller.

When you’re done, you realize you also got the same thing to do for RatioCalculator and MoneyCalculator, so you copy paste and you are good to go.

Wait a minute, the client prefers that the error message is displayed in orange rather than red. So you change the color and copy-paste the code again.

## Take another step back

**What if, instead of fixing a bug, you answered a need?**

Why did the client put 0 in the form? Because he made a mistake.

What is needed here?

- Is it **only** making sure the time that the user inputs in “speedCalculationForm” is ≠ 0?
- Is it **only** making sure the “speedCalculationForm” contains valid data?
- **Or is it validating all user inputs?**

So what about a validation library for example?

Waaaaait! Don’t go and write one yourself! For the love of god, take a step back, breathe, and look at what already exists.

## Needs

We, programmers, love being technical. When your client or your boss thinks out loud about what he wants, we can’t help but imagine how we could implement it.

But we need be able to take a step back. If we want to be really good in our jobs, **we have to understand the needs before thinking about solutions**. And that takes a lot of effort.

Does the client really need “a blinking button that moves away when you try to click on it?” or does he need something else, something that he doesn’t know about and that you could help him define? And the same goes for yourself! Do you really need to open a file and write some infos in there, or do you simply need a logging system?

Take a step back, try and see the big picture. Because one may be a very good programmer, but code’s purpose is to answer a need.

Not a *"fix the bugs in the bug tracker"* kind of need, but rather a *"I want an application will help me calculate speed based on input data, and if I type in invalid data then for fuck’s sake just tell me don’t go and calculate some weird results"*.
