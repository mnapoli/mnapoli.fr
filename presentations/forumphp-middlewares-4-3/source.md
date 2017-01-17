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

<br>

- Stratify ([github.com/stratifyphp](https://github.com/stratifyphp))
- [externals.io](http://externals.io/)
- [isitmaintained.com](https://isitmaintained.com/)

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

## Accéder à d'autre composants (dépendances)

- singleton
- variables globales
- service locator/registry
- proxy statique ("facades" Laravel)
- injection de dépendances
- ...

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---
class: main-title

# Architecture d'applications HTTP

## *« quand, comment et quoi est appelé ? »*

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
    $event = new Event(...);
    $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);
    if ($event->hasResponse()) {
        return $event->getResponse();
    }

    $controller = $request->attributes->get('_controller');
    $args = /* resolve controller arguments */;

    $response = call_user_func_array($controller, $args);
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
function foo() { … }

function () { … }

class Foo
{
    public function bar() { … }
}

class Foo
{
    public function __invoke() { … }
}
```
]
.right-block[
```php
$callable = 'foo';

$callable = function () { … }




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
    public function __invoke($request, callable $next)
    {
        $response = $next($request);
        
        // write to log
        
        return $response;
    }
}
```

---

```php
$middleware = function ($request, callable $next) {
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
$middleware = function ($request, callable $next) {
    $response = $next($request);
    
    // write to log
    
    return $response;
}
```

---

```php
$logger = function ($request, callable $next) {
    $response = $next($request);
    
    // write to log
    
    return $response;
}

$errorHandler = function ($request, callable $next) {
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
$p = new Pipe([
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
$p = new Pipe();

$p->pipe(function($request, $next) {
    ...
});
$p->pipe(function($request, $next) {
    ...
});
$p->pipe(function($request, $next) {
    ...
});
```
]

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* **(et $next)** et retourne une *réponse*.

---
class: main-title

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.

---

```php
$pipe = new Pipe([

    function ($request, $next) {
        // error handler
    },
    
    function ($request, $next) {
        // logger
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
    '/' => /* controller */,
    '/about' => /* controller */,
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
        '/' => /* controller */,
        '/about' => /* controller */,
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

$app->pipe(function (...) { ... });
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

```php
class Authentication
{
    public function __invoke($request, $next)
    {
        $auth = /* get token from headers */;
        
        $user = /* find user by token */;
        
        if (!$user) {
            return new Response('YOU SHALL NOT PASS!', 403);
        }
            
        $request = $request->withAttribute('user', $user);

        return $next($request, $response);
    }
}
```

---

## PSR-15

- [PSR-15](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md)
- [http-interop/http-middleware](https://github.com/http-interop/http-middleware)

```php
class MyMiddleware implements MiddlewareInterface
{
    public function process(
        RequestInterface $request,
        DelegateInterface $delegate
    ) {
        return $delegate->next($request);
    }
}
```

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
        '/' => /* controller */,
        '/article/{id}' => /* controller */,

        '/api/articles' => /* controller */,
        '/api/articles/{id}' => /* controller */,
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
        '/' => /* controller */,
        '/article/{id}' => /* controller */,
    ]),
]);

$api = new Pipe([
    new ErrorHandler(),
    new ForceHttps(),
    new Authentication(),
    new Router([
        '/api/articles' => /* controller */,
        '/api/articles/{id}' => /* controller */,
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
            '/api/articles' => /* controller */,
            '/api/articles/{id}' => /* controller */,
        ]),
    ]),
    '/{.*}' => new Pipe([
        new ErrorHandler(),
        new ForceHttps(),
        new MaintenanceMode(),
        new SessionMiddleware(),
        new DebugBar(),
        new Router([
            '/' => /* controller */,
            '/article/{id}' => /* controller */,
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
            '/api/articles' => /* controller */,
            '/api/articles/{id}' => /* controller */,
        ]),
    ]),
    '/' => new Pipe([
        new ErrorHandler(),
        new ForceHttps(),
        new MaintenanceMode(),
        new SessionMiddleware(),
        new DebugBar(),
        new Router([
            '/' => /* controller */,
            '/article/{id}' => /* controller */,
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
                '/api/articles' => /* controller */,
                '/api/articles/{id}' => /* controller */,
            ]),
        ]),
        '/' => new Pipe([
            new MaintenanceMode(),
            new SessionMiddleware(),
            new DebugBar(),
            new Router([
                '/' => /* controller */,
                '/article/{id}' => /* controller */,
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
    
    '/api/' => $expressive, // ou API Platform, Zend Apigility ?
    
    '/admin/' => function ($request) {
        set_global_variables($request);
        ob_start();
        $run_legacy_application();
        $html = ob_get_clean();
        return new HtmlResponse($html);
    },
]);
```

---
class: main-title

# Votre application

# *Votre architecture*

---
class: main-title

### Conclusion :

# Un *middleware* est quelque chose qui prend une *requête* et retourne une *réponse*.
