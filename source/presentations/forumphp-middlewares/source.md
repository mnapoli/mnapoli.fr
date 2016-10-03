class: title

# Les middlewares en PHP

---

Les middle-what ?

---
class: profile

.profile-picture[
    ![](img/profile.jpeg)
]

## Matthieu Napoli [github.com/mnapoli](https://github.com/mnapoli)

.company-logo[ [![](img/wizaplace.png)](https://wizaplace.com) ]

<div class="clear"></div>

.pull-right[ .small[ [Piwik](http://piwik.org) - 5500★ - 1,3% du web ] ]

- [PHP-DI](http://php-di.org/) - 725★ - 295 000⬇ - 2012
- [Couscous](http://couscous.io) - 400★
- [php-enum](https://github.com/myclabs/php-enum) - 240★ - 300 000⬇
- [DeepCopy](https://github.com/myclabs/DeepCopy) - 1 400 000⬇ *(utilisé par PHPUnit)*

.small[ *+ 80 autres dont des ratés comme BlackBox, Stratify, NumberTwo, Transform, ACL, Aspect-PHP, MetaModel, MetaConsole, procedure... :'(* ]

---

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

function (ServerRequestInterface $request) : ResponseInterface {
    return new Response();
}
```

---
class: user big

Bob développe des applications web

---
class: user big

Bob utilise un framework

---
class: user big

Bob est heureux

---
class: framework big

Alice développe des frameworks web

---
class: framework big

Alice est heureuse

---
class: user big

Mais sur certains projets, Bob est limité

---
class: framework big

Middlewares

---

.center[ ![](img/middleware.png) ]

---

```php
$response = middleware($request);
```

```php
function middleware($request) {
    return new Response('Hello');
}
```

---

```php
// index.php

$application = function (ServerRequestInterface $request) {
    return new TextResponse('Hello world!');
};

$response = $application(…);
…
```

---

```php
// index.php

$application = function (ServerRequestInterface $request) {
    return new TextResponse('Hello world!');
};

$response = $application(ServerRequestFactory::fromGlobals());
…
```

---

```php
// index.php

$application = function (ServerRequestInterface $request) {
    return new TextResponse('Hello world!');
};

$response = $application(ServerRequestFactory::fromGlobals());
(new SapiEmitter)->emit($response);
```

---

```ruby
$ cat /var/log/apache2/access.log \
        | grep 404 \
        | awk '{ print $7 }' \
        | sort \
        | uniq -c \
        | sort

   1 /blog/wp-content/uploads/2012/12/favicon.ico
   1 /favicon.ico
   1 /login?code=auie&state=auie
  10 /dreams/wp-content/uploads/2016/03/header-bg.png
  33 /description.xml
```

---

.center[ ![](img/step-1.png) ]

---

.center[ ![](img/step-3.png) ]

---

```php
$pipe = new Pipe([
    function ($request, $next) {
        ...
    },
    function ($request, $next) {
        ...
    },
    function ($request, $next) {
        ...
    },
]);
```

---

```php
$application = new Pipe([

    function ($request, $next) {
        try {
            return $next($request);
        } catch (\Exception $e) {
            return new TextResponse('Oops!', 500);
        }
    },
    
    function ($request, $next) {
        return new TextResponse('Hello world!');
    },
    
]);

$response = $application(ServerRequestFactory::fromGlobals());
(new SapiEmitter)->emit($response);
```
