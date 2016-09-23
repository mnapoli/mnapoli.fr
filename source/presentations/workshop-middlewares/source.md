class: title

# Les middlewares en PHP

---

Les middle-what ?

---

Framework vs Library

---

.center[ ![](img/middleware.png) ]

---

```php
$response = middleware($request);
```

---

## Symfony

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

## PSR-7

```
composer require psr/http-message
```

- `RequestInterface`
- `ServerRequestInterface`
- `ResponseInterface`
- ...

---

## PSR-7: immutability

```php
$request = $request->withQueryParams([
    'foo' => 'bar'
]);
```

```php
$response = $response->withHeader('Content-Length', 123);
```

```php
$response->getBody()->write('Hello');
```

---

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

function (ServerRequestInterface $request) : ResponseInterface {
    return new Response('Hello world!');
}
```

.small[
*[Zend Diactoros](https://github.com/zendframework/zend-diactoros)*
]

---
class: title

# Step 1

## write and run a middleware

---

.center[ ![](img/step-1.png) ]

---

## Step 1: write and run a middleware

Write your first middleware.

The web application (in `index.php`) should show "Hello world!" at [http://localhost:8000](http://localhost:8000/).

---

```php
use Zend\Diactoros\Response\TextResponse;

$application = function (ServerRequestInterface $request) {
    return new TextResponse('Hello world!');
};
```

---
class: title

# Step 2

## use the request

---

## Step 2: use the request

Use the request so that [http://localhost:8000/?name=Bob](http://localhost:8000/?name=Bob) shows "Hello Bob!"

.small[
*Bonus: [http://localhost:8000/](http://localhost:8000/) should still show "Hello world!"*
]

---

```php
$application = function (ServerRequestInterface $request) {
    $queryParams = $request->getQueryParams();
    
    $name = $queryParams['name'] ?? 'world';
    
    return new TextResponse('Hello ' . $name . '!');
};
```

---
class: title

# Step 3

## compose middlewares to handle errors nicely

---

```
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
function (ServerRequestInterface $request, callable $next) {
    return new TextResponse('Hello world!');
};
```

---

```php
function (ServerRequestInterface $request, $next) {
    // do something with $request
    return $next($request);
};

function (ServerRequestInterface $request, $next) {
    $response = $next($request);
    // do something with $response
    return $response;
};
```

---

```php
$wrappedMiddleware = function ($request, $next) {
    return new TextResponse('Hello world!');
};

$loggerMiddleware = function ($request, $next) {
    file_put_contents('access.log', 'New request!', FILE_APPEND);
    return $next($request);
};

$response = $loggerMiddleware($request, $wrappedMiddleware);
```

.center[?]

---

```php
$wrappedMiddleware = function ($request, $next) {
    return new TextResponse('Hello world!');
};

$loggerMiddleware = function ($request, $next) {
    file_put_contents('access.log', 'New request!', FILE_APPEND);
    return $next($request);
};

$next = function ($request) use ($wrappedMiddleware) {
    return $wrappedMiddleware($request, function () {
        return new TextResponse('Not Found', 404);
    });
};
$response = $loggerMiddleware($request, $next);
```

---

## Step 3: compose middlewares to handle errors

Assemble multiple middlewares with a `Pipe`.

```php
$application = new Pipe([
    function (...) { ... },
    function (...) { ... },
]);
```

Write an error handler middleware. Place it first in the pipe. It should catch exceptions thrown in next middlewares and show an error page.

.small[
*Bonus: write the error handler middleware as a class.*
]

---

```php
use Psr\Http\Message\ServerRequestInterface as Request;

$application = new Pipe([

    // Error handler
    function (Request $request, callable $next) {
        try {
            return $next($request);
        } catch (\Exception $e) {
            $m = $e->getMessage();
            return new TextResponse('Error: '.$m, 500);
        }
    },

    // Application
    function (Request $request, callable $next) {
        throw new Exception('Test');
    },

]);
```

---

```php
class ErrorHandler implements Middleware
{
    public function __invoke(Request $request, callable $next)
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            $whoops = $this->createWhoops();
            $output = $whoops->handleException($e);
            return new HtmlResponse($output, 500);
        }
    }

    private function createWhoops()
    {
        return ...;
    }
}
```

---
class: title

# Step 4

## split the flow with a router

---

.center[ ![](img/step-3.png) ]

---

.center[ ![](img/step-4.png) ]

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

---

![](img/fastroute.png)

---

## Step 4: split the flow with a router

Use the router to map URLs to handlers (aka controllers).

```php
$router = new Router([
    '/' => function () { ... },
    '/blog/' => function () { ... },
    '/article/{name}' => function () { ... },
]);
```

---

```php
$application = new Pipe([
  new ErrorHandler(),
  new Router([
  
    '/' => function () use ($container) {
        $articles = $container->articleRepository()->getArticles();
        $html = $container->twig()->render('home.html.twig', [
            'articles' => $articles,
        ]);
        return new HtmlResponse($html);
    },
    
    '/about' => function () use ($container) {
        return new HtmlResponse(
            $container->twig()->render('about.html.twig')
        );
    },
      
  ]),
]);
```

---

# Controller == Middleware

---
class: title

# Step 5

## Authentication middleware

---

.center[ ![](img/step-5.png) 

---

## HTTP Basic authentication

```
Authorization: Basic QWxhZGRpbjpPcGVuU2VzYW1l
```

---

```php
$header = $request->getHeaderLine('Authorization');
if (strpos($header, 'Basic') !== 0) {
    // No authentication found: 401
    ...
}

// Decode the username and password from the HTTP header
$header = explode(':', base64_decode(substr($header, 6)), 2);
$username = $header[0];
$password = isset($header[1]) ? $header[1] : null;

if (/* $username and $password are valid */) {
    // Authenticated
    ...
}

// Authentication failed: 403
...
```

---

## Step 5: authentication middleware

Write a middleware that checks for a valid HTTP "Basic" authentication before calling the next middleware.

Complete the existing `HttpBasicAuthentication` class.

Run tests with: `composer tests`

.small[
*Bonus: use the middleware in your application to prevent access to the whole website.*
]

---

```php
$header = $request->getHeaderLine('Authorization');
if (strpos($header, 'Basic') !== 0) {
    // No authentication found
    return new EmptyResponse(401, [
        'WWW-Authenticate' => 'Basic realm="Superpress"',
    ]);
}

// [...]

if (/* $username and $password are valid */) {
    return $next($request);
}

// Authentication failed
return new EmptyResponse(403);
```

---

```php
$application = new Pipe([
    new ErrorHandler(),
    new HttpBasicAuthentication([
        'user' => 'password',
    ]),
    new Router([
        ...
    ]),
]);
```

---
class: title

# Step 6

## nesting middlewares

---

## Step 6: nesting middlewares

Add an API to your application (JSON responses):

- `/api/articles` should return the list of articles
- `/api/time` should return the current `time()`

The API must require authentication (HTTP basic auth), but the website must be publicly accessible (no authentication anymore).

Remember the router or the middleware pipe are like any other middleware: you can nest them and use them several times.

---

```php
$application = new Pipe([
    new ErrorHandler(),
    
    new Router([
        '/' => function () { ... },
        '/about' => function () { ... },

        '/api/{path}' => new Pipe([
            new HttpBasicAuthentication(['user' => 'password']),
            
            new Router([
                '/api/articles' => function () {
                    return new JsonResponse(...);
                },
                '/api/time' => function () {
                    return new JsonResponse(time());
                },
            ]),
        ]),
    ]),
]);
```

---

```php
$application = new Pipe([
    new ErrorHandler(),
    
    new Router([
        '/' => function () { ... },
        '/about' => function () { ... },
    ]),
    
    new Pipe([
        new HttpBasicAuthentication(['user' => 'password']),
        
        new Router([
            '/api/articles' => function () {
                return new JsonResponse(...);
            },
            '/api/time' => function () {
                return new JsonResponse(time());
            },
        ]),
    ]),
]);
```

---
class: title

# Step 7

## request attributes

---

## Step 7: request attributes

Add an API endpoint (`/api/whoami`) that returns the user name.

You can pass the user name from the authentication middleware to the controller using request attributes.

---

```php
if (isset($this->users[$username]) && ($this->users[$username] === $password)) {
    // Authenticated

    // Store the username as a request attribute
    $request = $request->withAttribute('user', $username);

    // Call the next middleware
    return $next($request);
}
```

```php
new Router([
    ...
    '/api/whoami' => function ($request) {
        return new JsonResponse($request->getAttribute('user'));
    },
]),
```

---

.left-block[
Architecture:

- pipe
- router
- **prefix router**

Request data:

- session

Applications or modules:

- **maintenance page**
- debug toolbar & pages
- assets & medias ([Glide](http://glide.thephpleague.com/))
- "bit.ly"
- login/register pages
- back-office
]

.right-block[
Request/response pre/post-processors:

- authentication
- firewall/authorization
- HTTP cache headers
- response cache
- content/language negotiation
- **logging**
- CRSF protection
- rate limiting for APIs
- exception handler/error page
- force HTTPS, redirect to www., add trailing /, ...
- robots (X-Robots-Tag)
- IP restriction
]

---

.small[
```php
class PrefixRouter implements Middleware
{
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function __invoke($request, $next)
    {
        $path = $request->getUri()->getPath();

        foreach ($this->routes as $prefix => $middleware) {
            if (strpos($path, $prefix) === 0) {
                return $middleware($request, $next);
            }
        }

        return $next($request);
    }
}

$router = new PrefixRouter([
    '/api/' => function () { ... },
    '/admin/' => function () { ... },
]);
```
]

---

```php
class MaintenancePage implements Middleware
{
    public function __construct($inMaintenance)
    {
        $this->inMaintenance = $inMaintenance;
    }

    public function __invoke($request, $next)
    {
        if ($this->inMaintenance) {
            return new TextResponse(
                'Please come back later',
                501
            );
        }

        return $next($request);
    }
}
```

---

```php
class LoggerMiddleware implements Middleware
{
    public function __invoke($request, $next)
    {
        $before = microtime(true);

        $response = $next($request);
        
        $log = sprintf(
            'Request processed in %d seconds',
            microtime(true)-$before
        );
        file_put_contents('logs/access.log', $log, FILE_APPEND);

        return $response;
    }
}
```

---
class: title

# Frameworks

---

## Slim

```php
$app = new \Slim\App();

// Global middleware
$app->add(function ($request, $response, $next) {
	// ...
});

// Route middleware
$app->get('/', function ($request, $response, $args) {
	// controller
})->add(function ($request, $response, $next) {
    // middleware
});
```

---

## Zend Expressive/ZF3

```php
$app->pipe('/', function ($req, $res, $next) {
    // ...
});
```

---

## Silex

```php
$app->before(function (Request $request, Application $app) {
    // ...
});

$app->after(function (Request $request, Response $response) {
    // ...
});
```

---

## Laravel

```php
class MyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ...

        return $next($request);
    }

}
```

---

## Stratify

[github.com/stratifyphp](https://github.com/stratifyphp)

---

## [isitmaintained.com](https://isitmaintained.com/)

[index.php](https://github.com/mnapoli/IsItMaintained/blob/master/web/index.php)

.small[
```php
$app = pipe([
    ErrorHandlerMiddleware::class,
    MaintenanceMiddleware::class,
    
    router([
        '/'                                => [HomeController::class, 'home'],
        '/check/{user}/{repository}'       => [ProjectController::class, 'check'],
        '/project/{user}/{repository}'     => [ProjectController::class, 'project'],
        '/badge/{badge}/{user}/{repo}.svg' => [BadgeController::class, 'badge'],
    ]),
    
    // If no route matched
    Error404Middleware::class,
]);
```
]

---

## [externals.io](http://externals.io)

[http.php](https://github.com/mnapoli/externals/blob/master/res/http.php)

![](img/externals.png)

---

.small.scroll[
```php
Pipe([
    ErrorHandler
    Logger
    ForceHttps
    RobotsTxt

    // API
    Prefix('/api/v1/', Pipe([
        ApiErrorHandler // JSON responses
        Router([
            '/api/v1/user/authenticate' => Pipe([
                HttpBasicAuthentication
                AuthenticationController
            ])
        ])
        Router([
            // public APIs
        ])
        TokenAuthentication
        Router([
            // restricted APIs
        ])
    ])

    Session
    CRSF
    DebugBar

    PrefixRouter([

        '/admin/' => Pipe([
            NewRelic
            AdminAuthentication
            Router([
                // admin back-office routes
            ])
        ])

        '/vendor/' => Pipe([
            NewRelic
            VendorAuthentication
            Router([
                // vendor back-office routes
            ])
        ])

        '/' => Pipe([
            NewRelic
            Router([
                // front-office routes
            ])
        ])
    ])

])
```
]

---

## PSR-15

```php
interface ServerMiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $frame
    );
}
```

```php
interface DelegateInterface
{
    public function next(RequestInterface $request);
}
```
