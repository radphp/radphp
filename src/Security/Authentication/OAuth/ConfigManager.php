<?php

namespace Rad\Security\Authentication\OAuth;

/**
 * OAuth ConfigManager
 *
 * @package Rad\Security\Authentication\OAuth
 */
class ConfigManager
{
    protected static $configs = [];

    /**
     * Set OAuth config
     *
     * @param string $configId
     * @param string $provider
     * @param array  $options
     *
     * @throws Exception
     */
    public static function set($configId, $provider, array $options = [])
    {
        self::$configs[$configId] = new Config(OAuthProviderFactory::create($provider), $options);
    }

    /**
     * Get OAuth config
     *
     * @param string $configId
     *
     * @return null|Config
     */
    public static function get($configId)
    {
        if (array_key_exists($configId, self::$configs)) {
            return self::$configs[$configId];
        }

        return null;
    }

    /**
     * Check config is exist or not
     *
     * @param string $configId
     *
     * @return bool
     */
    public static function exist($configId)
    {
        return array_key_exists($configId, self::$configs);
    }
}
