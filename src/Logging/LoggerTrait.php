<?php

namespace Rad\Logging;

use Psr\Log\LogLevel;

/**
 * Logger Trait
 *
 * @package Rad\Logging
 */
trait LoggerTrait
{
    /**
     * Log
     *
     * @param string $message    Log message
     * @param string $level      Log level
     * @param array  $context    Log context
     * @param string $loggerName Logger name
     */
    public function log($message, $level = LogLevel::ERROR, array $context = [], $loggerName = 'default')
    {
        LoggerRegistry::getLogger($loggerName)->log($level, $message, $context);
    }
}
