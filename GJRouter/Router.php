<?php
namespace GJRouter;

use Psr\Log\LoggerInterface;
use Exception;
use stdClass;

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
    private string $request_params;
    private string $baseUri;

    /**
     * Constructor
     *
     * @param string $function_prefix
     * @param string $default_route_func
     * @param string $header
     * @param LoggerInterface|null $logger
     * @param string $baseUri
     * @throws Exception
     */
    public function __construct(string $function_prefix = '', string $default_route_func = '', string $header = 'Authorization', ?LoggerInterface $logger = null, string $baseUri = '')
    {
        $this->function_prefix = $function_prefix;
        $this->routes = [];
        $this->auth_ref = new stdClass();
        $this->default_route_func = $function_prefix . $default_route_func;
        $this->header = $header;
        $this->logger = $logger;
        $this->baseUri = $baseUri;

        $this->request_method = '';
        $this->request_uri = '';
        $this->request_headers = [];
        $this->request_json = null;
        $this->request_params = '';

        if (!is_null($this->logger))
        {
            $this->logger->info('GJRouter created');
        }

        try {
            $this->auth = new Auth($logger);
        }
        catch (Exception $e)
        {
            $this->auth = null;
            if (!is_null($this->logger))
            {
                $this->logger->error('Unable to initialise JWT', [$e->getMessage()]);
            }
            throw new Exception('Unable to initialise');
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
                $this->logger->error('Endpoint was empty, route not added');
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
                $this->logger->error('Function does not exist, route not added');
            }
            return false;
        }

        $r = new stdClass();
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
    public function route(?array $headers = []): bool
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

        if (!is_null($this->logger))
        {
            $this->logger->info('BaseURI', [$this->baseUri]);
        }

        if (!is_null($this->logger))
        {
            $this->logger->info('Raw request', [$this->request_method, $_SERVER['REQUEST_URI']]);
        }

        if ($this->baseUri == '')
        {
            $re = '/^(?P<route>\/[-\w]+)(?:\?(?P<params>[^#]*))?#?.*$/m';
        }
        else
        {
            $re = '/^\/'.$this->baseUri.'(?P<route>\/[-\w]+)(?:\?(?P<params>[^#]*))?#?.*$/m';
        }

        preg_match_all($re, (string) $_SERVER['REQUEST_URI'], $matches, PREG_SET_ORDER, 0);

        if (count($matches) === 1)
        {
            $this->request_uri = $matches[0]['route'];

            if (array_key_exists('params', $matches[0]))
            {
                $this->request_params = $matches[0]['params'];
            }

        }
        else
        {
            // if you can't parse it, just use the value
            /** @var string $this->request_uri */
            $this->request_uri = $_SERVER['REQUEST_URI'];

        }

        // get all the HTTP headers
        $this->request_headers = $headers ?? [];

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

            if ($route->auth === true)
            {

                // Does the appropriate header exist?

                if (array_key_exists($this->header, $this->request_headers) && !empty($this->request_headers[$this->header]))
                {

                    // Is the Auth object present?

                    if (!is_null($this->auth))
                        {
                            if ($this->auth->authenticate((string) $this->request_headers[$this->header], (bool) $route->admin))
                            {
                                // decoded & verified successfully
                                header('GJRouterReason: Verified successfully');

                                // run the function
                                call_user_func((string) $route->function, $this);

                                return true;
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

            if (!is_null($this->logger))
            {
                $this->logger->warning('No route found, trying default', [$hash]);
            }

            if ($this->default_route_func !== '' && function_exists($this->default_route_func)) {
                call_user_func($this->default_route_func, $this);
            }
            else
            {
                throw new Exception('Unable to route, no match and no default');
            }

        }

        return true;

    }

    /**
     * Get the routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Create a token
     *
     * @param array|null $claims
     * @return string|null
     */
    public function createToken(?array $claims): ?string
    {
        return (! is_null($this->auth))? $this->auth->createToken($claims) : null;
    }    

    /**
     *  Getter: method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->request_method;
    }

    /**
     * Getter: URI
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->request_uri;
    }

    /**
     * Getter: JSON
     *
     * @return object|null
     */
    public function getJson(): ?object
    {
        return $this->request_json;
    }

    /**
     * Getter: headers array
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->request_headers;
    }

}
