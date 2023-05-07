---
layout: post
title: "The Repository interface"
date: 2014-03-10 18:00
comments: true
categories: ddd php doctrine
---

Here is the definition of a repository for Eric Evans in his
[Domain Driven Design](http://books.google.fr/books/about/Domain_driven_Design.html?id=7dlaMs0SECsC&redir_esc=y) book:

> A *repository* represents all objects of a certain type as a conceptual set (usually emulated).
> **It acts like a collection**, except with more elaborate querying capability.
> [...]
> For each type of object that needs global access, **create an object that can provide the illusion
> of an in-memory collection** of all objects of that type.

While I love Doctrine, I really dislike
[their repository interface](https://github.com/doctrine/common/blob/master/lib/Doctrine/Common/Persistence/ObjectRepository.php)
because it doesn't look and act like a collection.
And that's too bad because Doctrine even provides a very good abstraction for collections
through the `doctrine/collection` project. It even supports filtering with criterias over collections **and** repositories.

I know that Doctrine is not targeted at Domain Driven Design only, but I think having a better repository interface
would still benefit the project and the users.

Here is a basic repository interface I tend to use instead:

```php
interface Repository
{
    function add($entity);

    function remove($entity);

    function count();

    /**
     * @throws NotFoundException
     * @return object
     */
    function get($id);

    /**
     * @return array
     */
    function find(Criteria $criteria);

    function toArray();
}
```

<!-- more -->

Of course, you should use this interface as a base and write your own repository interfaces for each aggregate root.

## Collection verbs

Why use verbs like`add` and `remove` instead of `load`, `save`, `delete`, `persist`, …?

Because those are persistence-related terms and have nothing to do in an interface
that is going to be used/extended in the domain layer.

## Get versus Find

I really dislike that you can only `find` in Doctrine: it will return null if no entity is found.

Most of the time, that is not what you want. You actually don't want to "search and find" the entity,
you just want to get it by its id and you assume it exists.

That's why you need to clearly differentiate between find and get:

- method starting with `get` should always return an entity, or throw an exception if not found
- method starting with `find` should always return a collection (that could be empty)

## Going further: the collection interface

I've said it already, but a repository should behave like a collection.
The interface shown above is not completely satisfying yet because it doesn't totally behave like a collection.
For example you can't iterate it.

So the best solution is simple: **write a sensible collection interface and have the repository extend it**.

```php
interface Collection extends Countable, IteratorAggregate, ArrayAccess
{
    function add($element);

    function remove($element);

    function clear();

    function contains($element);

    function get($key);

    function find(Criteria $criteria);

    function toArray();

    function slice($offset, $length = null);
}

interface Repository extends Collection
{
}
```

(this is a very simple version, not exhaustive at all)

Now this is much better.
**You can even type-hint against the collection and accept at the same time collections and repositories!**

You can then iterate or filter the collection without having to care what the object really is.
For example, you can write a `ProductCriteria` and use it both on the repository and collections of products.

Example: let's say you write a controller to display a product list:

```php
class ProductListController
{
    /**
     * @var ProductCollection
     */
    private $products;

    public function __construct(ProductCollection $products)
    {
        $this->products = $products;
    }

    public function listAction()
    {
        return new View('product-list.twig.html', [$this->products]);
    }
}
```

Here your controller is completely reusable. If you give it the `ProductRepository`, it can display the whole
product list. If you give it the list of favorite products of the user, it will work too.
Thank you dependency injection!

And the day PHP gets generics, that will be even nicer (`Collection<Product>`) but that's a debate for another day!
