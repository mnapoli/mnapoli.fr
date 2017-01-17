---
layout: post
title: "Using MetaModel and MetaConsole to debug your application"
date: 2013-09-13 16:08
comments: true
categories: php open-source metamodel
---

I started working a few months ago on [MetaModel](https://github.com/mnapoli/MetaModel), a language that enables to **traverse and operate your PHP model**.

Today I'm going to show you how you can use MetaModel through the [MetaConsole](https://github.com/mnapoli/MetaConsole) to debug your application.

![](/images/posts/metaconsole3.gif)

<!-- more -->

## Setup

To integrate the MetaConsole to your project, it's very simple using Composer:

```json
{
    "require-dev": {
        "mnapoli/metaconsole": "dev-master"
    }
}
```

Now I create a `bin/meta.php` file (name it as you like) in my application:

```php
#!/usr/bin/env php
<?php
use MetaModel\Bridge\Doctrine\EntityManagerBridge;
use MetaModel\Bridge\PHPDI\PHPDIBridge;
use MetaModel\MetaModel;

// Here I set up my application (bootstrap)
require_once __DIR__ . '/../application/init.php';

$metaModel = new MetaModel();

// Integrate with the Doctrine EntityManager
$entityManager = /* ... */;
$metaModel->addObjectManager(new EntityManagerBridge($entityManager));

// Integrate with PHP-DI container
$container = /* ... */;
$metaModel->addContainer(new PHPDIBridge($container));

$console = new MetaConsole\Application('MetaConsole', null, $metaModel);
$console->run();
```

As you can see, I can integrate the console with my ORM ([Doctrine 2](http://www.doctrine-project.org/) here) and my DI container ([PHP-DI](http://php-di.org/)). You can write bridges for other kinds of object managers or containers (and feel free to submit a Pull Request too).

## Using

Last week, I had a bug in my application, and it was very difficult to debug because it involved data, and that was not the kind of bug you can reproduce and fix using unit tests because it involved a lot of objects. Without MetaConsole, I would have had to dig through the database using phpMyAdmin or MySQL Workbench, writing queries and joining dozens of tables.

Instead, I launched MetaConsole, and I selected an object for which I had an ID (the ID was in the URL of the webpage I wanted to debug):

```
AF_Model_InputSet(562)
```

This query will select the entity `AF_Model_InputSet` with ID 562, and dump it to the screen. To do this, MetaModel queries Doctrine, which roughly translate to this PHP code:

```php
$entity = $entityManager->find('AF_Model_InputSet', 562);
```

Now that I had my object on screen, I could **traverse the object graph** (through associations, getters, arrays, …) with more precise queries:

![](/images/posts/metaconsole1.png)

In the end, I ended up finding my bug: one component was marked as "required" but had a number of required fields > 0 (not normal!). I fixed the code, and now I needed to rebuild some data to have everything in sync again (after my tests). That's the kind of operation that can't be done just on a single object through the website.

No problem, MetaModel let's you traverse an object graph, but also **call methods on objects and on services:**

![](/images/posts/metaconsole2.png)

## Conclusion

MetaModel is pretty much stable, though there are a lot of features I want to add (arguments in method calls, …).

On the other side, MetaConsole is still in development, and I hope to provide better integration with frameworks and a more enjoyable interface. If you are interested, you can try it (it's a development tool, so there's no risk since you shouldn't use it in production), and you can [improve it](https://github.com/mnapoli/MetaConsole).

And also, ideas are welcome!
