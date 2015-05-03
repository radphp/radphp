<?php

namespace Rad\Database;

use Cake\Database\Exception;
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
     */
    public static function get($name, array $config)
    {
        $driverClass = "Rad\\Database\\Driver\\" . ucfirst($name);

        if (!class_exists($driverClass)) {
            throw new Exception(sprintf('Driver "%s" does not exist.', $driverClass));
        }

        /** @var DriverInterface $driverInstance */
        $driverInstance = new $driverClass();
        $driverInstance->setConfig($config);

        return $driverInstance;
    }
}
