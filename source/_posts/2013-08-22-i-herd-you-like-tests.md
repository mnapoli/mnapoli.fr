---
layout: post
title: "I herd you like tests"
date: 2013-08-22 16:17
comments: true
categories:
---

So you chose to test your application using unit/functional tests.

**How do you ensure your tests do indeed test what you expect?**

Fear not! Here comes **TestsTester**!

<!-- more -->

## Use case

```php tests/MyTest.php
<?php
class MyTest extends PHPUnit_Framework_TestCase
{
    public function testDate()
    {
        $entry = new Entry();

        $this->assertNotNull($entry->getDate());
    }
}
```

Woops, I forgot to test that the date is in the past:

```php
$this->assertLessThanOrEqual(new DateTime(), $entry->getDate());
```

Now my sofware is filled with bugs!

## Usage

With **TestsTester**, I would have detected that:

```php teststests/MyTestTest.php
<?php
class MyTestTest extends TestsTester
{
    public function testTestDate()
    {
        $test = new Test('MyTest', 'testDate');

        $test->checkAssertNotNull('$entry->getDate()');
        $test->checkAssertLessThanOrEqual('new DateTime()', '$entry->getDate()');
    }
}
```

You can then run the test tests:

```bash
$ testtester teststests/
```

Result:

```
TestsTester 1.0.0

F

There was 1 failure:

1) MyTestTest::testTestDate
'MyTest::testDate' do not test that '$entry->getDate()' is less than or equal to 'new DateTime()'.

FAILURES!
Tests: 1, Assertions: 2, Failures: 1.
```

Give it a try and check out all the crazy features at [**TestsTester.com**](http://bit.ly/7JJSz8).

{% img /images/posts/yo-dawg.jpg 300 %}
