<?php

namespace Rad\Logging;

/**
 * Abstract Formatter
 *
 * @package Rad\Logging
 */
abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function format($level, $message, $time, array $context = []);

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    protected function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        return strtr($message, $replace);
    }
}
