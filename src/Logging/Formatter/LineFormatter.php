<?php

namespace Rad\Logging\Formatter;

use DateTime;
use Rad\Logging\AbstractFormatter;

/**
 * Line Formatter
 *
 * @package Rad\Logging\Formatter
 */
class LineFormatter extends AbstractFormatter
{
    protected $logFormat = "%time% [%level%] %message%\n";
    protected $timeFormat = 'd/M/Y G:i:s T';

    /**
     * Rad\Logging\Formatter\LineFormatter constructor
     *
     * @param string $logFormat  Log format
     * @param string $timeFormat Time format
     */
    public function __construct($logFormat = '', $timeFormat = '')
    {
        if ($logFormat) {
            $this->logFormat = $logFormat;
        }

        if ($timeFormat) {
            $this->timeFormat = $timeFormat;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format($level, $message, $time, array $context = [])
    {
        return strtr(
            $this->logFormat,
            [
                '%time%' => (new DateTime())->setTimestamp($time)->format($this->timeFormat),
                '%level%' => strtoupper($level),
                '%message%' => $this->interpolate($message, $context)
            ]
        );
    }
}
