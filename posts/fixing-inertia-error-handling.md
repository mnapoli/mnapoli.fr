---
layout: post
title: "Fixing error handling in Inertia.js"
date: 2025-03-31 10:32
comments: true
---

[Bref Cloud](https://bref.sh/cloud) is built with Laravel and [Inertia.js](https://inertiajs.com/). Inertia.js connects Laravel with Vue.js, making everything related to routing, auth, state management, and more, much easier to handle. **Inertia.js is awesome**.

However, there is one thing that I really dislike about it: how it handles server errors.

<!--more-->

![](/images/posts/inertia-error.gif)

When an error occurs on the server, **Inertia will show the error page in a modal**. That feels very weird. Even weirded when that modal shows because an async request failed.

The official docs suggest to customize the error page, but I want to get rid of the modal entirely.

**Let's replace the modal with toast notifications.**

I'm using [vue-toastification](https://github.com/Maronato/vue-toastification) for toast notifications. You can use any other library, but the idea is the same.

First, let's override how Laravel turns errors into HTTP responses in `bootstrap/app.php`. When we are in an Inertia request, instead of returning the error page, we will return a JSON response with the error message.

```php
return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $isServerError = in_array($response->getStatusCode(), [500, 503], true);
            $isInertia = $request->headers->get('X-Inertia') === 'true';
            // When in an Inertia request, we don't want to show the default error modal
            if ($isServerError && $isInertia) {
                $errorMessage = 'An internal error occurred, please try again. If the problem persists, please contact support.';
                // In local environment let's show the actual exception class & message
                if (app()->isLocal()) {
                    $errorMessage .= sprintf("\n%s: %s", get_class($exception), $exception->getMessage());
                }
                return response()->json([
                    'error_message' => $errorMessage,
                ], $response->getStatusCode());
            }

            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'flash.banner' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })->create();
```

Now that Laravel returns a custom JSON response, we need to handle it in Inertia. Let's add this to the `app.js` file:

```javascript
import { router } from '@inertiajs/vue3'

router.on('invalid', (event) => {
    const responseBody = event.detail.response?.data;
    if (responseBody?.error_message) {
        const toast = useToast()
        toast.error(responseBody.error_message);
        event.preventDefault();
    }
});

// ...
```

In short, if the server returns a JSON response with an `error_message` key, we will show it in a toast notification. Otherwise, we will let Inertia.js handle the error as usual.

![](/images/posts/inertia-error-2.png)

Much better!
