<?php

namespace Rad\Database;

/**
 * Connection Manager
 *
 * @package Rad\Database
 */
class ConnectionManager
{
    /**
     * @var Connection[]
     */
    protected static $connections = [];

    /**
     * Set connection
     *
     * @param string $name
     * @param string $driver
     * @param array  $config
     */
    public static function set($name, $driver, array $config = [])
    {
        self::$connections[$name] = new Connection($name, $driver, $config);
    }

    /**
     * Get connection
     *
     * @param string $name
     *
     * @return Connection
     * @throws Exception
     */
    public static function get($name)
    {
        if (!isset(self::$connections[$name])) {
            throw new Exception(sprintf('Connection "%s" does not exist.', $name));
        }

        return self::$connections[$name];
    }
}
