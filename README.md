# GJRouter

[![Total Downloads](https://img.shields.io/packagist/dt/newtovaux/gjrouter.svg)](https://packagist.org/packages/newtovaux/gjrouter)
[![Latest Stable Version](https://img.shields.io/packagist/v/newtovaux/gjrouter.svg)](https://packagist.org/packages/newtovaux/gjrouter)
![Continuous Integration](https://github.com/newtovaux/gjrouter/workflows/Continuous%20Integration/badge.svg)

A simple PHP Router incorporating JWT authenticator.

## Packagist

GJRouter is available at [Packagist](https://packagist.org/packages/newtovaux/gjrouter).
## Installation

Install the latest version with [Composer](https://getcomposer.org/):

```bash
$ composer require newtovaux/gjrouter
```

If you only need this library during development, for instance to run your project's test suite, then you should add it as a development-time dependency:

```bash
composer require --dev newtovaux/gjrouter
```

## Examples

See [examples/index.php](examples/index.php) file for a full example.

### Basic Usage

```php
<?php

require_once('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GJRouter\Router;

// Create a logger instance first, for example using monolog (https://github.com/Seldaek/monolog)

$log = new Logger('router');
$log->pushHandler(new StreamHandler(__DIR__.'/logs/info.log', Logger::INFO));
$log->pushHandler(new StreamHandler(__DIR__.'/logs/error.log', Logger::ERROR));

/**
    * Create the GJRouter\Router object, with the:
    *    - Name of the function prefix (optional)
    *    - Name of the default (fallback) route function (optional)
    *    - Name of the JWT bearer in HTTP Header (defaults to 'Authorization')
    *    - Logger instance to use (optional)
*/

$router = new Router('route_', 'route_default', 'Authorization', $log);

/**
    * Add some routes, using the:
    *    - URI
    *    - HTTP method (GET, POST, PUT or DELETE)
    *    - Function to call (which is automatically prefixed)
    *    - Whether you want to authenticate (bool)
    *    - Whether you want to check this route is only available for admins (bool)
*/

$router->addRoute('/api/auth', 'GET', 'auth', FALSE, FALSE);
$router->addRoute('/', 'GET', 'page', FALSE, FALSE);
$router->addRoute('/api/entity', 'GET', 'page', TRUE, FALSE);

// Route!

$router->route();

// Add the functions that the GJRouter\Router will call:

function route_auth(string $method, string $uri, $headers, $jsondata): void 
{
    $log->information('hello');
    echo json_encode(['hello']);
}

function route_page(string $method, string $uri, array $headers, $jsondata): void 
{
    echo '<html><body><h1>Page</h1></body></html>'; // Output some HTML
}

function route_default(string $method, string $uri, array $headers, $jsondata): void 
{
    header("Location: /"); // Redirect browser
    $log->information('default');
}
```

## About

### Source

Source is available on GitHub: https://github.com/newtovaux/gjrouter

### Requirements

- GJRouter `^1.0` is tested with PHP 7.4 or above, but *may* work with earlier versions.

### License

GJRouter is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


## Development

Create autoload:

    composer dump-autoload -o

Static Analysis:

    ./vendor/bin/psalm --show-info=true

Run tests:

    ./vendor/bin/phpunit tests

