<?php

namespace Rad\Error;

use Exception;

/**
 * Abstract Handler
 *
 * @package Rad\Error
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var ErrorHandler
     */
    protected $error;

    /**
     * Set error handler
     *
     * @param ErrorHandler $error
     */
    public function setErrorHandler(ErrorHandler $error)
    {
        $this->error = $error;
    }

    /**
     * Get error handler
     *
     * @return ErrorHandler
     */
    public function getErrorHandler()
    {
        return $this->error;
    }

    /**
     * {$@inheritdoc}
     */
    abstract public function handle(Exception $exception);
}
