<?php

namespace Rad\Logging;

/**
 * Logger Registry
 *
 * @package Rad\Logging
 */
class LoggerRegistry
{
    protected static $loggers = [];

    /**
     * Register logger
     *
     * @param string      $name   Logger name
     * @param Logger|null $logger Logger instance
     *
     * @return Logger
     */
    public static function register($name, Logger $logger = null)
    {
        if (!$logger) {
            $logger = new Logger($name);
        }

        self::$loggers[$name] = $logger;

        return $logger;
    }

    /**
     * Has logger
     *
     * @param string $name Logger name
     *
     * @return bool
     */
    public static function has($name)
    {
        return array_key_exists($name, self::$loggers);
    }

    /**
     * Un register logger
     *
     * @param string $name Logger name
     *
     * @return bool
     */
    public static function unRegister($name)
    {
        if (self::has($name)) {
            unset(self::$loggers[$name]);

            return true;
        }

        return false;
    }

    /**
     * Get logger instance
     *
     * @param string $name Logger name
     *
     * @return Logger|null
     */
    public static function getLogger($name)
    {
        if (self::has($name)) {
            return self::$loggers[$name];
        }

        return null;
    }

    /**
     * Get available loggers
     *
     * @return Logger[]
     */
    public static function getLoggers()
    {
        return self::$loggers;
    }
}
