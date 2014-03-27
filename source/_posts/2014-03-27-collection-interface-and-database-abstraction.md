---
layout: post
title: "The Collection interface and Database abstraction"
date: 2014-03-27 18:00
comments: true
categories: php doctrine
---

This article logically follows the previous: [The Repository interface](/repository-interface/). In there I suggested a better interface for repositories based on a Collection interface so that repositories could be manipulated like collections.

This article goes further in that idea and presents an API to abstract database access behind collections of objects.

But first let's start with Doctrine Criterias.

<!-- more -->

## What are Doctrine Criterias?

When you use Doctrine, you end up interacting with list of objects in two ways:

- through repositories or the entity manager
- through associations

At the entity manager or repository level, you can write **DQL queries** (or use the query builder that will generate them for you). This is the most powerful API Doctrine provides to interact with the database.

But inside objects you don't have access to the entity manager or the repository, because the model shouldn't know about the persistence. So what do you do when you want to **filter an association without loading all the objects?** And furthermore: what do you do **if the association is already loaded in memory?** That would be stupid to issue a DB query when you could just filter the objects in memory.

That's where Doctrine introduced the concept of **Criteria**. It's an API that allows you to filter an association with an abstract "query" (≠ DB query) that you apply to a collection. What's good is:

- if the collection is already loaded, the filtering happens in memory with PHP
- if the collection is not loaded, the filtering is done in a DB query

> The Criteria has a limited matching language that works both on the SQL and on the PHP collection level.
> This means you can use collection matching interchangeably, independent of in-memory or sql-backed collections.

That's awesome!

Example of how to use it:

```php
$criteria = Criteria::create()
    ->where(Criteria::expr()->eq('birthday', '1982-02-17'))
    ->orderBy(array('username' => Criteria::ASC))
    ->setFirstResult(0)
    ->setMaxResults(20);

$birthdayUsers = $userCollection->matching($criteria);
```

The Criteria API is obviously much more limited than DQL, but **it's completely decoupled from persistence**: you can use it in your model without polluting it with persistence problems, and you can use it against collections, loaded or not.

And the greatest thing of all: Doctrine developers didn't stop here, they also made the Repositories be compatible with those Criterias. Which means the same API whether it's a repository or not. If you read my previous article, you know how much I like that.

## What's wrong then?

Well something has to be wrong else I wouldn't be writing this, and you wouldn't be wasting your time reading it.

- you cannot filter on associations of objects inside the collection, i.e. `JOIN` (which is pretty common)
- you cannot perform updates or deletes in bulk without loading the objects first (like set `published = true` for all articles in this collection or repository)
- the API uses persistence words, like "where", "order by"… It's not so much a biggy but still, it's not perfect
- you cannot chain criterias and have only 1 DB query: 1 Criteria = 1 query

The last point is a bit vague so let me show you an example:

```php
class Blog
{
    public function getPublishedArticles()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('published', true));
        return $this->articles->matching($criteria);
    }
}

$articles = blog->getPublishedArticles();

$criteria = Criteria::create()
    ->setFirstResult(0)
    ->setMaxResults(20);
$articlesToDisplay = $articles->matching($criteria);
```

Here all the published articles will be loaded in memory, and then the first 20 results will be kept.

## The Collection interface

Before diving in a better alternative to criterias, let's start back with an awesome Collection interface:

```php
interface Collection extends Countable, IteratorAggregate, ArrayAccess
{
    function add($element);

    function get($key);

    function contains($element);

    function remove($element);

    function clear();

    function toArray();

    function count();

    function filter(Expr $predicate);

    function sort($field, $direction = 'ASC');

    function slice($offset, $length = null);

    function map(Closure $function);
}
```

(let's also assume that the Repository interface also extends that interface :) )

## Filtering!

This interface looks a lot like the Doctrine Collection interface, except that `filter` doesn't take a `Closure` anymore but an "expression", which is where the fun begin. Because it's not PHP code but an expression object (like the Criteria expression object), we can apply the same filtering in memory and in database.

Now we are get out of the scope of the interface and have a look at the implementations: **what if `filter`, `sort` and `slice` where lazy?**

What I mean is these methods would return a new "lazy" collection that would only be loaded/computed if used. That allows some pretty nice chaining!

Example:

```php
$results = $articles->filter(Expr::eq('title', 'Hello'))
        ->filter(Expr::eq('author', $john))
        ->sort('date', 'DESC');

// Perform a COUNT() query in database
echo count($results) . ' results';

// Fetches only 10 results from the database
$pageResults = $results->slice(0, 10);

// Executes on loaded objects, no DB query here
$allAuthors = $pageResults->map(function (Article $article) {
    return $article->getAuthor();
});
```

This code would only issue 2 queries:

```sql
-- Count the total number of articles
SELECT COUNT(art.id) FROM Article art
INNER JOIN User ON User.id = art.author_id
WHERE art.title = 'Hello'
ORDER BY art.date DESC

-- Fetch 10 articles for the current page
SELECT ... FROM Article art
INNER JOIN User ON User.id = art.author_id
WHERE art.title = 'Hello'
ORDER BY art.date DESC
LIMIT 0, 10
```

## What about updating and deleting?

In the same spirit, you could imagine an API to update or delete items from a collection or repository:

```php
// Delete Bob's articles
$articles = $articles->filter(Expr::eq('author', $bob));
$articles->clear();

// Publish Alice's articles
$articles = $articles->filter(Expr::eq('author', $alice));
$articles->apply(Expr::set('published', true));
```

Here the delete and update queries will include the previous filtering, such that there is only 1 query executed for Bob, and 1 for Alice.

Of course, that is if the objects are not loaded in memory. If they are, then the objects are updated.

## Conclusion

This article was just an idea thrown around for discussion. There is no library available, or repository containing any code.

To sum up all this quickly:

- a repository is a collection (and much more too)
- you can manipulate collections and chain filtering, sorting, etc... in a very readable way while knowing you don't trigger dozens of DB queries
- the API shown here could be a more powerful alternative to Criterias
- it can also provide a simpler alternative to DQL/Query Builder with the benefit of being persistence agnostic and usable in entities
- it could allow pagination and sorting in the controllers much more easily (you wouldn't need to add these as parameters in your repositories for example)
- it would allow for more optimized database access (counting the collection would just issue a COUNT query for example)

### Drawbacks

I can't finish that article without expressing what I consider as the major drawback behind all this:
you are a little tiny bit persistent-aware, because you don't filter your collections with PHP code but with
an arbitrary expression language.

That doesn't mean your code is coupled to the persistence layer though because it also completely works in memory,
but you deliberately don't use PHP code because you *know* that it's not optimized to load all the objects from the
database and then filter them with PHP.

I would say **that's an acceptable compromise**, because let's be honest, there is no perfect solution.
**You can't apply PHP code to a SQL database** so you have to compromise somewhere…

### Penumbra

Or can you? ;)

That's the bonus note on which I will finish.
There is another approach (rather controversial) taken by a new project named **[Penumbra](https://github.com/TimeToogo/Penumbra)**.
Instead of playing with "expressions" to filter collections, it allows to filter using PHP code! It then parses that code
to turn it into SQL code. Pretty audacious, but apparently it works (if you keep it reasonable) :)

Example:

```php
$MiddleAgedUsersRequest = $UserRepository->Request()
        ->Where(function (User $User) {
            return $User->GetAge() > 20 && $User->GetAge() < 50 ;
        })
        ->OrderByDescending(function (User $User) { return $User->GetLastLoginDate(); });

$SomeActiveMiddleAgedUsers = $MiddleAgedUsersRequest->AsArray();
```

Will result in:

```sql
SELECT Users.* FROM Users
WHERE Users.Age > 20 AND Users.Age < 50
ORDER BY Users.LastLoginDate DESC;
```

I will not comment on whether this is good or not, because honestly I don't know.
It seems like it's heavily tested and it's a serious project, so it's not just a POC or hack.
I'll let you make your own mind :)
