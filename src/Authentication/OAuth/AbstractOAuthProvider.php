<?php

namespace Rad\Authentication\OAuth;

use Psr\Http\Message\RequestInterface;

/**
 * Abstract OAuth Provider
 *
 * @package Rad\Authentication\OAuth
 */
abstract class AbstractOAuthProvider
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Set config
     *
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get config
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Config http request
     *
     * @param RequestInterface $request Http request
     *
     * @return RequestInterface
     */
    protected function request(RequestInterface $request)
    {
        return $request->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', 'RadPHP-OAuth');
    }

    /**
     * Get authorize uri
     *
     * @return string
     */
    abstract public function getAuthorizeUri();

    /**
     * Get access token
     *
     * @return string
     * @throws Exception
     */
    abstract public function getAccessToken();

    /**
     * Get user detail
     *
     * @param string $token Access token
     *
     * @return User
     * @throws Exception
     */
    abstract public function getUser($token);
}
