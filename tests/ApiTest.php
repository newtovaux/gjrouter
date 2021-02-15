<?php declare(strict_types=1);

require_once('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase
{

    private Logger $log;

    public function setUp(): void
    {
        // Setup the logger
        $this->log = new Logger('phpunit');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/logs/info.log', Logger::INFO));
        $this->log->pushHandler(new StreamHandler(__DIR__.'/logs/error.log', Logger::ERROR));

        // Setup the environment variables
        $_ENV['HMAC_KEY'] = 'mBC5v1sOKVvbdEitdSBenu59nfNfhwkedkJVNabosTw';
        $_ENV['ISSUER'] = 'http://www.example.com';
        $_ENV['AUDIENCE'] = 'http://www.example.com';

    }

    public function testInstantiate(): void
    {
        $this->assertInstanceOf(
            GJRouter\Router::class,
            new GJRouter\Router()
        );
    }

    public function testInstantiateWithParams(): void
    {        

        $this->assertInstanceOf(
            GJRouter\Router::class,
            new GJRouter\Router('route_', 'route_default', 'Authorization', $this->log)
        );
    }

    public function testRouteNoRequestMethodOrURI(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);

        $router = new GJRouter\Router('route_', 'route_default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertFalse($router->route());

    }

    public function testRouteWithRequestMethodNoURI(): void
    {

        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_SERVER['REQUEST_URI']);

        $router = new GJRouter\Router('route_', 'route_default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertFalse($router->route());

    }

    public function testRouteWithRequestURINoMethod(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        $_SERVER['REQUEST_URI'] = '/';

        $router = new GJRouter\Router('route_', 'route_default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertFalse($router->route());

    }

    public function testRouteWithRequestMethodAndURI(): void
    {

        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', 'route_default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertTrue($router->route());

    }

    public function testGetters(): void
    {

        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', 'route_default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertIsArray($router->getHeaders());

        $this->assertNull($router->getJson());

        $this->assertIsString($router->getMethod());

        $this->assertIsString($router->getUri());

    }

    public function testCreateToken(): void
    {

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

        $token = $router->createToken(['user' => []]);

        $this->assertIsString($token);

        $this->log->info($token);

    }

    public function testRoute(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

        $router->addRoute('/api/test', 'GET', 'test', FALSE, FALSE);

        $routes = $router->getRoutes();

        $this->assertIsArray($routes);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->log->info(print_r($routes, TRUE));

        $router->route();
    }

}

function route_test(GJRouter\Router $route): void
{
    
}

function route_default(GJRouter\Router $route): void
{
    
}
