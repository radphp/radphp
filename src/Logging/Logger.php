<?php

namespace Rad\Logging;

use SplObjectStorage;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

/**
 * Logger
 *
 * @package Rad\Logging
 */
class Logger implements LoggerInterface
{
    /**
     * @var SplObjectStorage
     */
    protected $adapters;
    protected $begun = false;
    protected $transaction = [];

    /**
     * Rad\Logging\Logger constructor
     *
     * @param string $name Logger name
     */
    public function __construct($name)
    {
        $this->adapters = new SplObjectStorage();
    }

    /**
     * Attach adapter
     *
     * @param AdapterInterface $adapter
     */
    public function attachAdapter(AdapterInterface $adapter)
    {
        $this->adapters->attach($adapter);
    }

    /**
     * Detach adapter
     *
     * @param AdapterInterface $adapter
     */
    public function detachAdapter(AdapterInterface $adapter)
    {
        $this->adapters->detach($adapter);
    }

    /**
     * Contains adapter
     *
     * @param AdapterInterface $adapter
     *
     * @return bool
     */
    public function containsAdapter(AdapterInterface $adapter)
    {
        return $this->adapters->contains($adapter);
    }

    /**
     * Begin transaction
     */
    public function begin()
    {
        $this->begun = true;
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->begun = false;
        $this->transaction = [];
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        foreach ($this->transaction as $trans) {
            $this->internalLog($trans['level'], $trans['message'], $trans['time'], $trans['context']);
        }

        $this->rollback();
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->begun) {
            $this->transaction[] = [
                'level' => $level,
                'message' => $message,
                'time' => time(),
                'context' => $context
            ];
        } else {
            $this->internalLog($level, $message, time(), $context);
        }
    }

    /**
     * Internal log
     *
     * @param string $level   Log level
     * @param string $message Log message
     * @param int    $time    Log time
     * @param array  $context Log context
     */
    protected function internalLog($level, $message, $time, array $context = [])
    {
        $this->adapters->rewind();
        while ($this->adapters->valid()) {
            /** @var AdapterInterface $adapter */
            $adapter = $this->adapters->current();
            $adapter->log($level, $message, $time, $context);

            $this->adapters->next();
        }
    }
}
