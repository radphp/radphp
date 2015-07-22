<?php

namespace Rad\Logging;

/**
 * Formatter Interface
 *
 * @package Rad\Logging
 */
interface FormatterInterface
{
    /**
     * Format log
     *
     * @param string $level   Log level
     * @param string $message Log message
     * @param int    $time    Log time
     * @param array  $context Log context
     *
     * @return string
     */
    public function format($level, $message, $time, array $context = []);
}
