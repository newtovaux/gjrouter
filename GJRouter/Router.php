<?php
namespace GJRouter;

use Psr\Log\LoggerInterface;
use GJRouter\Auth;

class Router
{

    private string $function_prefix;
    private array $routes;
    private object $auth_ref;
    private string $default_route_func;
    private ?LoggerInterface $logger;
    private ?Auth $auth;
    private string $header;
    private string $request_method;
    private string $request_uri;
    private array $request_headers;
    private ?object $request_json;

    /**
     * Constructor
     *
     * @param string $function_prefix
     * @param string $default_route_func
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $function_prefix = '', string $default_route_func = '', string $header = 'Authorization', ?LoggerInterface $logger = null)
    {
        $this->function_prefix = $function_prefix;
        $this->routes = [];
        $this->auth_ref = new \stdClass();
        $this->default_route_func = $function_prefix . $default_route_func;
        $this->header = $header;
        $this->logger = $logger;

        $this->request_method = '';
        $this->request_uri = '';
        $this->request_headers = [];
        $this->request_json = null;

        if (!is_null($this->logger))
        {
            $this->logger->info('Started');
        }

        try {
            $this->auth = new Auth($logger);
        }
        catch (\Exception $e)
        {
            $this->auth = null;
            if (!is_null($this->logger))
            {
                $this->logger->error('Unable to initialise JWT', [$e->getMessage()]);
            }
            throw new \Exception('Unable to initialise');
        }
        
    }

    /**
     * addRoute method
     *
     * @param string $endpoint
     * @param string $method
     * @param string $function
     * @param boolean $auth
     * @param boolean $admin
     * @return boolean
     */
    public function addRoute(string $endpoint, string $method, string $function, bool $auth = false, bool $admin = false): bool
    {

        // check params being passed in

        if ($endpoint === '') {
            if (!is_null($this->logger))
            {
                $this->logger->error('Enpoint was empty, route not added');
            }

            return false;
        }

        // check the method

        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
            if (!is_null($this->logger))
            {
                $this->logger->error('Invalid method, route not added');
            }
            return false;
        }

        // check the function exists

        $full_func_name = $this->function_prefix . $function;

        if (!function_exists($full_func_name)) {
            if (!is_null($this->logger))
            {
                $this->logger->error('Function doesn\'t exist, route not added');
            }
            return false;
        }

        $r = new \stdClass();
        $r->endpoint = $endpoint;
        $r->method = $method;
        $r->function = $full_func_name;
        $r->auth = $auth;
        $r->admin = $admin;

        // Simple hash, allow collisions, something like "GET/api/one"
        $hash = $r->method . $r->endpoint;

        if (! array_key_exists($hash, $this->routes))
        {
            $this->routes[$hash] = $r;
        }
        else
        {
            if (!is_null($this->logger))
            {
                $this->logger->error('Route collision, route not added');
            }
        }
        
        return true;

    }

    /**
     * Main routing method
     *
     * @return bool
     */
    public function route(): bool
    {
        // get the request method

        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
            if (!is_null($this->logger))
            {
                $this->logger->error('REQUEST_METHOD not set');
            }
            return false;

        }

        /** @var string $this->request_method */
        $this->request_method = $_SERVER['REQUEST_METHOD'];

        // get the request URI

        if (!array_key_exists('REQUEST_URI', $_SERVER)) {
            if (!is_null($this->logger))
            {
                $this->logger->error('REQUEST_URI not set');
            }
            return false;
        }

        /** @var string $this->request_uri */
        $this->request_uri = $_SERVER['REQUEST_URI'];

        if (!is_null($this->logger))
        {
            $this->logger->info('Request', [$this->request_method, $this->request_uri]);
        }

        // get all the HTTP headers
        $this->request_headers = [];
        if(!function_exists('getallheaders'))
        {
            if (!is_null($this->logger))
            {
                $this->logger->warning('Headers not available');
            }
        }
        else
        {
            $this->request_headers = getallheaders();
            if ($this->request_headers == FALSE)
            {
                $this->request_headers = [];
            }
        }

        // request data
        /** @var object|null $this->request_json */
        $this->request_json = json_decode(file_get_contents('php://input'));

        // simple hash
        $hash = $this->request_method . $this->request_uri;

        // get match

        if (array_key_exists($hash, $this->routes))
        {
            /** @var object $route  */
            $route = $this->routes[$hash];

            // is auth required?

            if ($route->auth == true)
            {

                // Does the appropriate header exist?

                if (array_key_exists($this->header, $this->request_headers) && !empty($this->request_headers[$this->header]))
                {

                    // Is the Auth object present?

                    if (!is_null($this->auth))
                        {
                            if ($this->auth->authenticate((string) $this->request_headers[$this->header], (bool) $route->admin))
                            {
                                // decoded & verified sucesfully
                                header('GJRouterReason: Verified successfully');

                                // run the function
                                call_user_func((string) $route->function, $this);
                            }
                            else
                            {
                                header('GJRouterReason: Failed JWT verification');
                                if (!is_null($this->logger))
                                {
                                    $this->logger->warning('Failed JWT verification', [$this->request_method, $this]);
                                }
                            }
                        }
                        else
                        {
                            if (!is_null($this->logger))
                            {
                                $this->logger->error('Auth requested, but no auth object present');
                            }
                            return false;
                        }                        
                        
                }
                else
                {
                    header('GJRouterReason: Token not present');
                    if (!is_null($this->logger))
                    {
                        $this->logger->warning('Failed JWT verification', [$this->request_method, $this->request_uri, $route]);
                    }
                }

                // Unauthorized client
                http_response_code(401);

                return false;

            }
            else
            {
                call_user_func((string) $route->function, $this);
            }

        }
        else
        {

            if ($this->default_route_func !== '' && function_exists($this->default_route_func)) {
                call_user_func($this->default_route_func, $this);
            }

        }

        return true;

    }

    public function createToken(?array $claims): ?string
    {
        return (! is_null($this->auth))? $this->auth->createToken($claims) : null;
    }    

    public function getMethod(): string
    {
        return $this->request_method;
    }

    public function getUri(): string
    {
        return $this->request_uri;
    }

    public function getJson(): ?object
    {
        return $this->request_json;
    }

    public function getHeaders(): array
    {
        return $this->request_headers;
    }

}
