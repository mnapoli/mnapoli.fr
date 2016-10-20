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

- [externals.io](http://externals.io/)
- [isitmaintained.com](https://isitmaintained.com/)
- [github.com/stratifyphp](https://github.com/stratifyphp)

---

# middle-what ?

---

class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---

# Singleton

---
class: main-title

# Un *singleton* est une classe qui a *une seule instance*.

---

> Accéder à d'autre composants (dépendances).

- singleton
- variables globales
- service locator/registry
- proxy statique ("facades" Laravel)
- injection de dépendances

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---
class: main-title

# Architecture d'applications HTTP

## « quand, comment et quoi est appelé ? »

---

- **routing**

--
- authentification/firewall
- logging
- cache
- headers de cache HTTP
- session
- page de maintenance
- assets/medias
- rate limiting
- forcer HTTPS
- restriction par IP
- content negotiation
- language negotiation
- ...

---
class: title

# Symfony

---

```php
try {
    $event = new Event($request, ...);
    $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);
    if ($event->hasResponse()) {
        return $event->getResponse();
    }

    $controller = $request->attributes->get('_controller');
    $controllerArguments = $this->resolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $controllerArguments);
} catch (\Exception $e) {
    $response = /* generate error response (error page) */;
}
```

---
class: title

# Events

---
class: title

# Stack

## 2013

---

```php
$kernel = new AppKernel();

$request = Request::createFromGlobals();

$response = $kernel->handle($request);

$response->send();
```

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---

```php
interface HttpKernelInterface
{
    /**
     * @return Response
     */
    public function handle(Request $request, ...);
}
```

---

## Stack [stackphp.com](http://stackphp.com/)

![](img/stack-conventions.png)

---

```php
class Middleware implements HttpKernelInterface
{
    public function __construct(HttpKernelInterface $next)
    {
        $this->next = $next;
    }

    public function handle(Request $request, …)
    {
        // do something before
        
        if (/* I want to */) {
            $response = $this->next->handle($request, …);
        } else {
            $response = new Response('Youpida');
        }
        
        // do something after
        
        return $response;
    }
}
```

---

```php
class LoggerMiddleware implements HttpKernelInterface
{
    public function __construct(HttpKernelInterface $next)
    {
        $this->next = $next;
    }

    public function handle(Request $request, …)
    {
        $response = $this->next->handle($request, …);
        
        // write to log
        
        return $response;
    }
}
```

---

```php
$kernel = new LoggerMiddleware(
    new AppKernel()
);

$request = Request::createFromGlobals();

$response = $kernel->handle($request);

$response->send();
```

---
class: center-image

## Onion style

![](img/onion.png)

.small[ [stackphp.com](http://stackphp.com/) ]

---

```php
$kernel = new HttpCache(
    new CorsMiddleware(
        new LoggerMiddleware(
            new AppKernel()
        ),
    ),
    new Storage(...)
);
```

---

- utilisation complexe
- hors de l'application
- spécifique Symfony

---
class: title

# PSR-7

## Mai 2015

---

## PSR-7

```
composer require psr/http-message
```

- `RequestInterface`
- `ServerRequestInterface`
- `ResponseInterface`
- ...

---
class: title

# Callable

---

[PHP callables](http://php.net/manual/en/language.types.callable.php)

.left-block[
```php
function foo() { ... }

function () { ... }

class Foo
{
    public function bar() { ... }
}

class Foo
{
    public function __invoke() { ... }
}
```
]
.right-block[
```php
$callable = 'foo';

$callable = function () { ... }




$callable = [new Foo(), 'bar'];




$callable = new Foo();

$callable();
```
]

---

```php
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LoggerMiddleware
{
    public function __construct(callable $next)
    {
        $this->next = $next;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $next = $this->next;
        $response = $next($request);
        
        // write to log
        
        return $response;
    }
}
```

---

```php
class LoggerMiddleware
{
    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $response = $next($request);
        
        // write to log
        
        return $response;
    }
}
```

---

```php
$middleware = function (ServerRequestInterface $request, callable $next) {
    $response = $next($request);
    
    // write to log
    
    return $response;
}
```

---

## "Middleware PSR-7"

```php
$middleware = function ($request, $response, callable $next) {
    $response = $next($request, $response);
    
    // write to log
    
    return $response;
}
```

---

```php
$middleware = function (ServerRequestInterface $request, callable $next) {
    $response = $next($request);
    
    // write to log
    
    return $response;
}
```

---

```php
$logger = function (ServerRequestInterface $request, callable $next) {
    $response = $next($request);
    
    // write to log
    
    return $response;
}

$errorHandler = function (ServerRequestInterface $request, callable $next) {
    try {
        return $next($request);
    } catch (\Throwable $e) {
        return new TextResponse('Oops!', 500);
    }
}

// ?
```

---
class: title

# Pipe

---

```ruby
$ cat access.log | grep 404 | awk '{ print $7 }' | sort | uniq -c | sort
```

---
class: center-image

![](img/pipe.png)

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

.small[ [Pipe.php](https://github.com/mnapoli/workshop-middlewares/blob/step-8/src/Middleware/Pipe.php) ]
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
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* **(et $next)** et retourne une *réponse*.

---

```php
$pipe = new Pipe([

    function ($request, $next) { // error handler
        try {
            return $next($request);
        } catch (\Throwable $e) {
            return new TextResponse('Oops!', 500);
        }
    },
    
    function ($request, $next) { // logger
        $response = $next($request);
        
        // write to log
        
        return $response;
    },
    
]);
```

---

```php
$pipe = new Pipe([
    new ErrorHandler(),
    new Logger(),
]);
```

---

```php
$router = new Router([
    '/' => function () {
        return new TextResponse('Hello world!');
    },
    '/about' => function () {
        return new TextResponse('This super website is sponsored by AFUP!');
    },
]);
$response = $router->route($request);
```

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---
class: center-image

![](img/router.png)

---

```php
$pipe = new Pipe([
    new ErrorHandler(),
    new Logger(),
    new Router([
        '/' => function () {
            return new TextResponse('Hello world!');
        },
        '/about' => function () {
            return new TextResponse('This super website is sponsored by AFUP!');
        },
    ]),
]);
```

---
class: title

# Frameworks

---

## Zend Expressive/ZF3

```php
$app = Zend\Expressive\AppFactory::create();

$app->pipe(function (...) {
    // middleware
});
$app->pipe(new MyMiddleware());
$app->pipe('nom-de-service');

$app->get('/', function () {
    // controller
});

// ...
$app->run();
```

---

## Slim

```php
$app = new Slim\App();

$app->add(function (...) {
	// middleware
});

$app->get('/', function () {
	// controller
})->add(function (...) {
    // route middleware
});
```

---
class: center-image

![](img/route-middleware.png)

---

## Laravel

```php
class MyMiddleware
{
    public function handle(Request $request, $next)
    {
        // ...
    }
}

class Kernel extends HttpKernel
{
    protected $middleware = [
        MyMiddleware::class,
    ];
    
    ...
}
```

---
class: title

# Middlewares

---
class: center-image

[![](img/oscarotero-middlewares.png)](https://github.com/oscarotero/psr7-middlewares)

[github.com/oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares)

---
class: title

# Architecture

---

```php
$application = new Pipe([
    new ErrorHandler(),
    new ForceHttps(),
    new MaintenanceMode(),
    new SessionMiddleware(),
    new DebugBar(),
    new Authentication(),
    
    new Router([
        '/' => function () { ... },
        '/article/{id}' => function () { ... },
        '/api/articles' => function () { ... },
        '/api/articles/{id}' => function () { ... },
    ]),
]);
```

---

```php
$website = new Pipe([
    new ErrorHandler(),
    new ForceHttps(),
    new MaintenanceMode(),
    new SessionMiddleware(),
    new DebugBar(),
    new Router([
        '/' => function () { ... },
        '/article/{id}' => function () { ... },
    ]),
]);
$api = new Pipe([
    new ErrorHandler(),
    new ForceHttps(),
    new Authentication(),
    new Router([
        '/api/articles' => function () { ... },
        '/api/articles/{id}' => function () { ... },
    ]),
]);
```

---

```php
$application = new Router([
    '/api/{.*}' => new Pipe([
        new ErrorHandler(),
        new ForceHttps(),
        new Authentication(),
        new Router([
            '/api/articles' => function () { ... },
            '/api/articles/{id}' => function () { ... },
        ]),
    ]),
    '/{.*}' => new Pipe([
        new ErrorHandler(),
        new ForceHttps(),
        new MaintenanceMode(),
        new SessionMiddleware(),
        new DebugBar(),
        new Router([
            '/' => function () { ... },
            '/article/{id}' => function () { ... },
        ]),
    ]),
]);
```

---

```php
$application = new PrefixRouter([
    '/api/' => new Pipe([
        new ErrorHandler(),
        new ForceHttps(),
        new Authentication(),
        new Router([
            '/api/articles' => function () { ... },
            '/api/articles/{id}' => function () { ... },
        ]),
    ]),
    '/' => new Pipe([
        new ErrorHandler(),
        new ForceHttps(),
        new MaintenanceMode(),
        new SessionMiddleware(),
        new DebugBar(),
        new Router([
            '/' => function () { ... },
            '/article/{id}' => function () { ... },
        ]),
    ]),
]);
```

---

```php
$application = new Pipe([
    new ErrorHandler(),
    new ForceHttps(),
    new PrefixRouter([
        '/api/' => new Pipe([
            new Authentication(),
            new Router([
                '/api/articles' => function () { ... },
                '/api/articles/{id}' => function () { ... },
            ]),
        ]),
        '/' => new Pipe([
            new MaintenanceMode(),
            new SessionMiddleware(),
            new DebugBar(),
            new Router([
                '/' => function () { ... },
                '/article/{id}' => function () { ... },
            ]),
        ]),
    ]),
]);
```

---

```php
$expressive = Zend\Expressive\AppFactory::create();
$expressive->...

$slim = new Slim\App();
$slim->...

$application = new PrefixRouter([
    '/dashboard/' => $slim,
    '/api/' => $expressive,
    '/admin/' => function () {
        $legacy = LegacyApplication::init();
        ob_start();
        $legacy->run();
        $html = ob_get_clean();
        return new HtmlResponse($html);
    },
]);
```

---
class: title

# Et maintenant ?

---

## PSR-15

- [PSR-15](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md)
- [http-interop/http-middleware](https://github.com/http-interop/http-middleware)

```php
class MyMiddleware implements MiddlewareInterface
{
    public function process(RequestInterface $request, DelegateInterface $delegate)
    {
        return $delegate->next($request);
    }
}
```

---
class: center-image

![](img/middlewares-vs-events.png)

---
class: main-title

### Conclusion :

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---
class: main-title

# Les *middlewares* permettent de mieux controler *l'architecture* des applications HTTP.

---

# TODO :

- attributes
- PSR-15
- middlewares PSR-7
