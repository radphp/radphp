<?php

namespace Rad\Database;

use Rad\Database\Driver\DriverInterface;

/**
 * Driver Factory
 *
 * @package Rad\Database
 */
class DriverFactory
{
    /**
     * Get driver
     *
     * @param string $name
     * @param array  $config
     *
     * @return DriverInterface
     * @throws Exception
     */
    public static function get($name, array $config)
    {
        $driverClass = "Rad\\Database\\Driver\\" . ucfirst($name);

        if (!class_exists($driverClass)) {
            throw new Exception(sprintf('Driver "%s" does not exist.', $driverClass));
        }

        return new $driverClass($config);
    }
}
