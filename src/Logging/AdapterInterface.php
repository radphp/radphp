<?php

namespace Rad\Logging;

/**
 * Adapter Interface
 *
 * @package Rad\Logging
 */
interface AdapterInterface
{
    /**
     * Logs with an arbitrary level.
     *
     * @param string $level   Log level
     * @param string $message Log message
     * @param int    $time    Log time
     * @param array  $context Log context
     *
     * @return null
     */
    public function log($level, $message, $time, array $context = []);

    /**
     * Sets the levels the adapter should log.
     *
     * @param array $logLevels Log levels
     */
    public function setLogLevels(array $logLevels = []);

    /**
     * Get the levels the adapter should log.
     *
     * @return array
     */
    public function getLogLevels();
}
