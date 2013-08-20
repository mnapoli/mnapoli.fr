---
layout: post
title: "Introduction to Dependency Injection with a real life example"
date: 2012-12-06 20:00
comments: true
external-url:
categories: php dependency-injection
---

This example is an introduction to the **Dependency Injection** concept. It is based on the PHP library [PHP-DI](http://mnapoli.github.com/PHP-DI/).

## Classic implementation

Given you have:

```php
class GoogleMapsService {
    public function getCoordinatesFromAddress($address) {
        // calls Google Maps webservice
    }
}

class OpenStreetMapService {
    public function getCoordinatesFromAddress($address) {
        // calls OpenStreetMap webservice
    }
}
```

The classic way of doing things is:

```php
class StoreService {
    public function getStoreCoordinates($store) {
        $geolocationService = new GoogleMapsService();
        // or $geolocationService = GoogleMapsService::getInstance() if you use singletons
        return $geolocationService->getCoordinatesFromAddress($store->getAddress());
    }
}
```

Now we want to use the OpenStreetMapService instead of GoogleMapsService, how do we do? We have to change the code of StoreService, and all the other classes that use GoogleMapsService.

**Without dependency injection, your classes are tightly coupled with their dependencies.**

<!-- more -->

## Dependency injection implementation

The StoreService now uses dependency injection:

```php
class StoreService {
    /**
     * @Inject
     * @var GeolocationService
     */
    private $geolocationService;

    public function getStoreCoordinates($store) {
        return $this->geolocationService->getCoordinatesFromAddress($store->getAddress());
    }
}
```

And the services are defined using an interface:

```php
interface GeolocationService {
    public function getCoordinatesFromAddress($address);
}

class GoogleMapsService implements GeolocationService {
    public function getCoordinatesFromAddress($address) {
        // calls Google Maps webservice
    }
}

class OpenStreetMapService implements GeolocationService {
    public function getCoordinatesFromAddress($address) {
        // calls OpenStreetMap webservice
    }
}
```

If you use [PHP-DI](http://mnapoli.github.com/PHP-DI/) (a PHP dependency injection library), you then configure which implementation will be used:

```php
$container->set('GeolocationService')
          ->bindTo('OpenStreetMapService');
```

If you change your mind, there’s just one line of configuration to change.
