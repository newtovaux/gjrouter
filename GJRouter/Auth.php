<?php
namespace GJRouter;

use Lindelius\JWT\Algorithm\HMAC\HS256;
use Lindelius\JWT\JWT;
use Psr\Log\LoggerInterface;

class Auth extends JWT
{
    use HS256;
    public static $leeway = 10;
    private string $hmac;
    private string $issuer;
    private string $audience;
    private ?LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        // Get the environment variables

        if (array_key_exists('HMAC_KEY', $_ENV) && array_key_exists('ISSUER', $_ENV) && array_key_exists('AUDIENCE', $_ENV)) {
            $this->hmac = (string) $_ENV['HMAC_KEY'];
            $this->issuer = (string) $_ENV['ISSUER'];
            $this->audience = (string) $_ENV['AUDIENCE'];
        } else {
            throw new \Exception('Environment variables missing');
        }
    }

    /**
     * Authenticate method
     *
     * @param string $jwttoken
     * @param boolean $needsadmin
     * @return boolean
     */
    public function authenticate(string $jwttoken, bool $needsadmin = false): bool
    {

        try {

            /** @var object $jwt */
            if ($jwt = $this->decode($jwttoken))
            {
                if ($jwt->verify($this->hmac, ['aud' => $this->audience, 'iss' => [$this->issuer]]))
                {
                    /** @var object $jwt->user */
                    if ($needsadmin)
                    {

                        if (($jwt->getClaim('user')) && (property_exists($jwt->user, 'admin')))
                        {
                            if ($jwt->user->admin)
                            {
                                // decoded, verified, needed admin, and admin present
                                return true;
                            }
                            else
                            {
                                if (!is_null($this->logger))
                                {
                                    $this->logger->warning('Needed admin - Not admin');
                                }
                                header('GJRouterAuthReason: Needed admin - Not admin');
                            }
                        }
                        else
                        {
                            if (!is_null($this->logger))
                            {
                                $this->logger->warning('Needed admin - Property user->admin does not exist in JWT');
                            }
                            header('GJRouterAuthReason: Property user->admin does not exist in JWT');
                        }
                    }
                    else
                    {
                        // decoded, verified, didn't need admin
                        return true;
                    }
                }
                else
                {
                    if (!is_null($this->logger))
                    {
                        $this->logger->warning('Unable to verify JWT signature');
                    }
                    header('GJRouterAuthReason: Unable to verify JWT signature');
                }
            }
            else
            {
                if (!is_null($this->logger))
                {
                    $this->logger->warning('Unable to decode JWT');
                }
                header('GJRouterAuthReason: Unable to decode JWT');
            }

        } catch (\Exception $e)
        {
            header('GJRouterAuthReason: JWT Exception - ' . $e->getMessage());
        }

        return false;

    }

}
