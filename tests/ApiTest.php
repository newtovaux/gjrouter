<?php declare(strict_types=1);

require_once('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;

/** @psalm-suppress PropertyNotSetInConstructor */
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
        $this->log->info('Test: ' . __FUNCTION__);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            new GJRouter\Router()
        );
    }

    public function testInstantiateWithParams(): void
    {        
        $this->log->info('Test: ' . __FUNCTION__);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            new GJRouter\Router('route_', 'default', 'Authorization', $this->log)
        );
    }

    public function testRouteNoRequestMethodOrURI(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertFalse($router->route());

    }

    public function testRouteWithRequestMethodNoURI(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_SERVER['REQUEST_URI']);

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertFalse($router->route());

    }

    public function testRouteWithRequestURINoMethod(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        unset($_SERVER['REQUEST_METHOD']);
        $_SERVER['REQUEST_URI'] = '/';

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertFalse($router->route());

    }

    public function testRouteWithRequestMethodAndURI(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->expectOutputString('DEFAULT');

        $this->assertTrue($router->route());

    }

    public function testGetters(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

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
        $this->log->info('Test: ' . __FUNCTION__);

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log);

        $token = $router->createToken(['user' => []]);

        $this->assertIsString($token);

        $this->log->info($token);

    }

    public function testDefault(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);
        $_SERVER['REQUEST_URI'] = '/api/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // default
        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log, 'api');

        $routes = $router->getRoutes();

        $this->assertEquals(0, count($routes));

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->expectOutputString('DEFAULT');

        $router->route();
    }

    public function testNoDefault(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        $_SERVER['REQUEST_URI'] = '/api/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // no default
        $router = new GJRouter\Router('route_', '', 'Authorization', $this->log, 'api');

        $routes = $router->getRoutes();

        $this->assertEquals(0, count($routes));

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->expectException(Exception::class);

        $router->route();
    }

    public function testRoute(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        $_SERVER['REQUEST_URI'] = '/api/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log, 'api');

        $router->addRoute('/test', 'GET', 'test', FALSE, FALSE);

        $routes = $router->getRoutes();
        
        /** @psalm-suppress RedundantCondition */
        $this->assertIsArray($routes);

        $this->assertEquals(1, count($routes));

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->log->info('Routes', [print_r($routes, TRUE)]);

        $this->expectOutputString('TEST');

        $router->route();
    }

    public function testBaseURI(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        $_SERVER['REQUEST_URI'] = '/api/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', 'default', 'Authorization', $this->log, 'api');

        $router->addRoute('/test', 'GET', 'test', FALSE, FALSE);

        $routes = $router->getRoutes();
        
        /** @psalm-suppress RedundantCondition */
        $this->assertIsArray($routes);

        $this->assertEquals(1, count($routes));

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->log->info(print_r($routes, TRUE));

        $this->expectOutputString('TEST');

        $router->route();
    }

    public function testEmptyBaseURI(): void
    {
        $this->log->info('Test: ' . __FUNCTION__);

        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router = new GJRouter\Router('route_', '', 'Authorization', $this->log, '');

        $router->addRoute('/test', 'GET', 'test', FALSE, FALSE);

        $routes = $router->getRoutes();
        
        /** @psalm-suppress RedundantCondition */
        $this->assertIsArray($routes);

        $this->assertEquals(1, count($routes));

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->log->info(print_r($routes, TRUE));

        $this->expectOutputString('TEST');

        $router->route();
    }

}

function route_test(GJRouter\Router $route): void
{
    echo 'TEST';
}

function route_default(GJRouter\Router $route): void
{
    echo 'DEFAULT';
}
