# GJRouter

A simple PHP Router incorporating JWT authenticator

## Development

Create autoload:

    composer dump-autoload -o

Static Analysis:

    ./vendor/bin/psalm --show-info=true

Run tests:

    ./vendor/bin/phpunit tests

## Usage Example

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

    /**
    * Undocumented function
    *
    * @param string $method
    * @param string $uri
    * @param array $headers
    * @param mixed $jsondata
    * @return void
    */
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