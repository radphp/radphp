<?php

namespace Rad\Logging;

use Rad\Logging\Formatter\LineFormatter;

/**
 * Abstract Adapter
 *
 * @package Rad\Logging
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $logLevels = [];

    /**
     * {@inheritdoc}
     */
    abstract public function log($level, $message, $time, array $context = []);

    /**
     * Set formatter
     *
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Get formatter
     *
     * @return FormatterInterface
     */
    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = new LineFormatter();
        }

        return $this->formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogLevels()
    {
        return $this->logLevels;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogLevels(array $logLevels = [])
    {
        $this->logLevels = $logLevels;
    }
}
