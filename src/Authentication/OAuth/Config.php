<?php

namespace Rad\Authentication\OAuth;

/**
 * OAuth Config
 *
 * @package Rad\Authentication\OAuth
 */
class Config
{
    protected $provider;
    protected $options = [];

    /**
     * Rad\Authentication\OAuth\Config constructor
     *
     * @param AbstractOAuthProvider $provider
     * @param array                 $options
     */
    public function __construct(AbstractOAuthProvider $provider, array $options)
    {
        $this->setProvider($provider);
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @param string $name
     *
     * @return null|mixed
     */
    public function get($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
    }

    /**
     * Get client id
     *
     * @return null|string
     */
    public function getClientId()
    {
        if (isset($this->options['client_id'])) {
            return strval($this->options['client_id']);
        }

        return null;
    }

    /**
     * Get client secret
     *
     * @return null|string
     */
    public function getClientSecret()
    {
        if (isset($this->options['client_secret'])) {
            return strval($this->options['client_secret']);
        }

        return null;
    }

    /**
     * Get redirect uri
     *
     * @return null|string
     */
    public function getRedirectUri()
    {
        if (isset($this->options['redirect_uri'])) {
            return strval($this->options['redirect_uri']);
        }

        return null;
    }

    /**
     * Get scope
     *
     * @return array
     */
    public function getScope()
    {
        if (isset($this->options['scope'])) {
            return (array)$this->options['scope'];
        }

        return [];
    }

    /**
     * Get state
     *
     * @return null|string
     */
    public function getState()
    {
        if (isset($this->options['state'])) {
            return strval($this->options['state']);
        }

        return null;
    }

    /**
     * Get provider
     *
     * @return AbstractOAuthProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set provider
     *
     * @param AbstractOAuthProvider $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        $this->provider->setConfig($this);
    }
}
