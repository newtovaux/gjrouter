# GJRouter

[![Total Downloads](https://img.shields.io/packagist/dt/newtovaux/gjrouter.svg)](https://packagist.org/packages/newtovaux/gjrouter)
[![Latest Stable Version](https://img.shields.io/packagist/v/newtovaux/gjrouter.svg)](https://packagist.org/packages/newtovaux/gjrouter)

A simple PHP Router incorporating JWT authenticator.

## Installation

Install the latest version with

```bash
$ composer require newtovaux/gjrouter
```

## Basic Usage

```php
    <?php

    require_once('vendor/autoload.php');

    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    use GJRouter\Router;

    // setup logger

    $log = new Logger('router');
    $log->pushHandler(new StreamHandler(__DIR__.'/logs/info.log', Logger::INFO));
    $log->pushHandler(new StreamHandler(__DIR__.'/logs/error.log', Logger::ERROR));


    $router = new Router('route_', 'route_default', $log);

    $router->addRoute('/api/auth', 'GET', 'auth', FALSE, FALSE);
    $router->addRoute('/', 'GET', 'page', FALSE, FALSE);
    $router->addRoute('/api/entity', 'GET', 'page', TRUE, FALSE);


    $router->route();

    function route_auth(string $method, string $uri, $headers, $jsondata): void 
    {
        error_log('hello');
        echo 'hello';
    }

    function route_page(string $method, string $uri, array $headers, $jsondata): void 
    {
        echo '<h1>Page</h1>';
    }

    function route_default(string $method, string $uri, array $headers, $jsondata): void 
    {
        error_log('default');
        echo 'default';
    }
```

## Documentation

Coming soon. I promise.

## About

### Requirements

- GJRouter `^1.0` is tested with PHP 7.4 or above.

## Development

Create autoload:

    composer dump-autoload -o

Static Analysis:

    ./vendor/bin/psalm --show-info=true

Run tests:

    ./vendor/bin/phpunit tests

