<?php declare(strict_types=1);

require_once('vendor/autoload.php');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase
{

    private $log;

    public function setUp(): void
    {
        $this->log = new Logger('phpunit');
        $this->log->pushHandler(new StreamHandler(__DIR__.'/logs/info.log', Logger::INFO));
        $this->log->pushHandler(new StreamHandler(__DIR__.'/logs/error.log', Logger::ERROR));

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
            new GJRouter\Router('route_', 'route_default', $this->log)
        );
    }

    public function testRouteNoRequestMethodOrURI(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);

        $router = new GJRouter\Router('route_', 'route_default', $this->log);

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

        $router = new GJRouter\Router('route_', 'route_default', $this->log);

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

        $router = new GJRouter\Router('route_', 'route_default', $this->log);

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

        $router = new GJRouter\Router('route_', 'route_default', $this->log);

        $this->assertInstanceOf(
            GJRouter\Router::class,
            $router
        );

        $this->assertTrue($router->route());

    }

}




