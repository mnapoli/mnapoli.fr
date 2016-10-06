class: main-title

### Conclusion :

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---
class: profile

.profile-picture[
    ![](img/profile.jpeg)
]

## Matthieu Napoli [github.com/mnapoli](https://github.com/mnapoli)

.company-logo[ [![](img/wizaplace.png)](https://wizaplace.com) ]

---

# middle-what ?

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---
class: full-image

![](img/space.jpg)

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

function middleware(ServerRequestInterface $request) : ResponseInterface {
    return new Response();
}
```

---

## Zend Diactoros [github.com/zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros)

![](img/diactoros.png)

---

## Request

```php
$request = new ServerRequest(
    $_SERVER,
    $_FILES,
    new Uri(...),
    $_SERVER['REQUEST_METHOD'],
    'php://input',
    $headers,
    $_COOKIE,
    $_GET,
    $_POST,
    $_SERVER['SERVER_PROTOCOL']
);
```

```php
$request = ServerRequestFactory::fromGlobals();
```

---

## Response

```php
foreach ($response->getHeaders() as $header => $values) {
    foreach ($values as $value) {
        header("$header: $value");
    }
}
echo $response->getBody();
```

```php
(new SapiEmitter)->emit($response);
```

---

.center[ ![](img/middleware.png) ]

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

.browser-mockup[ Hello world! ]

---

```php
$application = function (ServerRequestInterface $request) {
    try {
        return new TextResponse('Hello world!');
    } catch (\Throwable $e) {
        return new EmptyResponse('Oops!', 500);
    }
};

$response = $application(ServerRequestFactory::fromGlobals());
(new SapiEmitter)->emit($response);
```

---

```php
$application = function (ServerRequestInterface $request) {
    return new TextResponse('Hello world!');
};

$errorHandler = function (ServerRequestInterface $request) use ($application) {
    try {
        return $application($request);
    } catch (\Throwable $e) {
        return new EmptyResponse('Oops!', 500);
    }
};

$response = $errorHandler(ServerRequestFactory::fromGlobals());
(new SapiEmitter)->emit($response);
```

---

```php
$application = function (ServerRequestInterface $request) {
    return new TextResponse('Hello world!');
};

$errorHandler = function (ServerRequestInterface $request, callable $next) {
    try {
        return $next($request);
    } catch (\Throwable $e) {
        return new EmptyResponse('Oops!', 500);
    }
};

$response = $errorHandler(ServerRequestFactory::fromGlobals(), $application);
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

## Unix philosophy

- Write programs that do one thing and do it well.
- Write programs to work together.
- Write programs to handle text streams, because that is a universal interface.

.small[ Peter H. Salus ]

---

```ruby
$ cat access.log | grep 404 | awk '{ print $7 }' | sort
```

---

.left-block[
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
]

--

.right-block[
```php
$pipe = new Pipe();

$pipe->pipe(function ($request, $next) {
    ...
});
$pipe->pipe(function ($request, $next) {
    ...
});
$pipe->pipe(function ($request, $next) {
    ...
});
```
]

---

```php
class Pipe
{
    private $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = function ($request) use ($middleware, $next) {
                return $middleware($request, $next);
            };
        }

        return $next($request);
    }
}
```

---

```php
$pipe = new Pipe([

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
```

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

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

$next = function () {
    throw new Exception('No middleware handled the response');
};
$response = $application(ServerRequestFactory::fromGlobals(), $next);
(new SapiEmitter)->emit($response);
```

---

```php
function (ServerRequestInterface $request, callable $next) {

    $url = $request->getUri()->getPath();
    
    if ($url === '/login') {
        return /* login page */;
    } elseif ($url === '/dashboard') {
        return /* dashboard page */;
    }
    
    return $next($request);
}
```
