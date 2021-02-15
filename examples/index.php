<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GJRouter\Router;

// Make sure the following Environment Variables are set, these are here just as examples, do not set them this way!

$_ENV['HMAC_KEY'] = 'mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw';
$_ENV['ISSUER'] = 'http://www.example.com';
$_ENV['AUDIENCE'] = 'http://www.example.com';

// Create a logger instance first, for example using monolog (https://github.com/Seldaek/monolog)

$log = new Logger('router');
$log->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

/**
    * Create the GJRouter\Router object, with the:
    *    - Name of the function prefix (optional)
    *    - Name of the default (fallback) route function (optional)
    *    - Logger instance to use (optional)
*/

try {
    $router = new Router('route_', 'default', 'Authorization', $log);

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
    $router->addRoute('/api/entity', 'POST', 'api', TRUE, FALSE);
    $router->addRoute('/api/admin', 'POST', 'api', TRUE, TRUE);
    $router->addRoute('/phpinfo', 'GET', 'info', FALSE, FALSE);


    error_log(php_sapi_name());

    if (php_sapi_name() == 'cli')
    {

        // Added for testing, in reality this would be provided by your webserver

        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

    }

    // Route!

    $router->route();
}
catch (\Exception $e)
{
    error_log('Exception thrown: '. $e->getMessage());
}

// Add the functions that the GJRouter\Router will call:

function route_auth(Router $route): void 
{
    error_log('hello');
    echo json_encode(['hello']);
}

function route_page(Router $route): void 
{
    error_log('page');
    echo '<html><body><h1>Page</h1></body></html>'; // Output some HTML
}

function route_api(Router $route): void 
{
    error_log('api');
    echo json_encode(['somedata']); // Output some JSON
}

function route_default(Router $route): void 
{
    echo '<html><body><h1>Error</h1><p>The page you requested does not exist.</p></body></html>'; // Output some HTML
    error_log('default');
}

function route_info(Router $route): void 
{
    echo phpinfo();
}