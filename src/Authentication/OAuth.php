<?php

namespace Rad\Authentication;

use Rad\Authentication\OAuth\Config;
use Rad\Authentication\OAuth\ConfigManager;
use Rad\Authentication\OAuth\Exception;

/**
 * OAuth
 *
 * @package Rad\Authentication
 */
class OAuth
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Rad\Authentication\OAuth constructor
     *
     * @param string $configId
     *
     * @throws Exception
     */
    public function __construct($configId)
    {
        if (ConfigManager::exist($configId)) {
            $this->config = ConfigManager::get($configId);
        } else {
            throw new Exception(sprintf('OAuth config id "%s" does not exist.', $configId));
        }
    }

    /**
     * Get authorize uri
     *
     * @return string
     */
    public function getAuthorizeUri()
    {
        return $this->config->getProvider()->getAuthorizeUri();
    }

    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->config->getProvider()->getAccessToken();
    }

    /**
     * Get user
     *
     * @param string $token Access token
     *
     * @return OAuth\User
     */
    public function getUser($token)
    {
        return $this->config->getProvider()->getUser($token);
    }
}