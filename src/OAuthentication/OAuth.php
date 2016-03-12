<?php

namespace Rad\OAuthentication;

use InvalidArgumentException;
use RuntimeException;

/**
 * OAuth
 *
 * @package Rad\OAuthentication
 */
class OAuth
{
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * Add provider
     *
     * @param string            $name     Provider name
     * @param ProviderInterface $provider Provider instance
     *
     * @return self
     */
    public function addProvider($name, ProviderInterface $provider)
    {
        if (!is_string($name) && empty($name)) {
            throw new InvalidArgumentException('Your provider name must be string and not empty.');
        }

        $this->providers[$name] = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @param string $name Provider name
     *
     * @return ProviderInterface
     */
    public function getProvider($name)
    {
        if (!array_key_exists($name, $this->providers)) {
            throw new RuntimeException(sprintf('Provider "%s" doesm\'t exist.', (string)$name));
        }

        return $this->providers[$name];
    }

    /**
     * has provider
     *
     * @param string $name Provider name
     *
     * @return bool
     */
    public function hasProvider($name)
    {
        return isset($this->providers[$name]);
    }

    /**
     * Remove provider
     *
     * @param string $name Provider name
     *
     * @return bool
     */
    public function removeProvider($name)
    {
        if ($this->hasProvider($name)) {
            unset($this->providers[$name]);

            return true;
        }

        return false;
    }

    /**
     * Get authorize uri
     *
     * @param string $providerName Provider name
     *
     * @return string
     */
    public function getAuthorizeUri($providerName)
    {
        return $this->getProvider($providerName)->getAuthorizeUri();
    }

    /**
     * Get access token
     *
     * @param string $providerName  Provider name
     * @param string $authorizeCode Authorize code
     *
     * @return array
     */
    public function getAccessToken($providerName, $authorizeCode)
    {
        return $this->getProvider($providerName)->getAccessToken($authorizeCode);
    }

    /**
     * Get user
     *
     * @param  string $providerName Provider name
     * @param string  $token        Access token
     *
     * @return User
     */
    public function getUser($providerName, $token)
    {
        return $this->getProvider($providerName)->getUser($token);
    }
}
