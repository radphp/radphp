<?php

namespace Rad\Error;

use Exception;

/**
 * Error Handler
 *
 * @package Rad\Error
 */
class ErrorHandler
{
    /**
     * @var HandlerInterface
     */
    protected $handler;
    protected $debug = false;
    protected $registered = false;
    protected $errorLevel = E_ALL;
    protected static $levelMap = [
        E_PARSE => 'error',
        E_ERROR => 'error',
        E_CORE_ERROR => 'error',
        E_COMPILE_ERROR => 'error',
        E_USER_ERROR => 'error',
        E_WARNING => 'warning',
        E_USER_WARNING => 'warning',
        E_COMPILE_WARNING => 'warning',
        E_RECOVERABLE_ERROR => 'warning',
        E_NOTICE => 'notice',
        E_USER_NOTICE => 'notice',
        E_STRICT => 'strict',
        E_DEPRECATED => 'deprecated',
        E_USER_DEPRECATED => 'deprecated',
    ];

    /**
     * Set handler
     *
     * @param AbstractHandler $handler
     *
     * @return self
     */
    public function setHandler(AbstractHandler $handler)
    {
        $handler->setErrorHandler($this);
        $this->handler = $handler;

        return $this;
    }

    /**
     * Set error level
     *
     * @param int $errorLevel Error level
     *
     * @return self
     */
    public function setErrorLevel($errorLevel)
    {
        $this->errorLevel = $errorLevel;

        return $this;
    }

    /**
     * Set debug mode
     *
     * @param bool $debug
     *
     * @return self
     */
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;

        return $this;
    }

    /**
     * Is debug mode
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Handle Exception
     *
     * @param Exception $exception
     */
    public function handleException(Exception $exception)
    {
        try {
            while (ob_get_level()) {
                ob_end_clean();
            }

            http_response_code($exception->getCode());
            echo $this->handler->handle($exception);

            return;
        } catch (Exception $e) {
            $message = sprintf(
                "[%s] %s\n%s",
                get_class($e),
                $e->getMessage(),
                $e->getTraceAsString()
            );

            trigger_error($message, E_USER_ERROR);
        }
    }

    /**
     * Handle Error
     *
     * @param int         $code    Error code
     * @param string      $message Error message
     * @param string|null $file    File on which error occurred
     * @param int|null    $line    Line that triggered the error
     * @param array|null  $context Error context
     *
     * @return bool
     */
    public function handleError($code, $message, $file = null, $line = null, $context = null)
    {
        if (error_reporting() === 0) {
            return false;
        }

        if (self::$levelMap[$code] === 'error') {
            return $this->handleFatalError($code, $message, $file, $line);
        }

        error_log($message, 0);

        return true;
    }

    /**
     * Handle Shutdown
     *
     * @return bool
     */
    public function handleShutdown()
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        $lastError = error_get_last();

        if (!is_array($lastError)) {
            return false;
        }

        $fatals = [
            E_USER_ERROR,
            E_ERROR,
            E_PARSE,
        ];

        if (!in_array($lastError['type'], $fatals, true)) {
            return false;
        }

        return $this->handleFatalError(
            $lastError['type'],
            $lastError['message'],
            $lastError['file'],
            $lastError['line']
        );
    }

    /**
     * Display a fatal error.
     *
     * @param int    $code    Error code
     * @param string $message Error message
     * @param string $file    File on which error occurred
     * @param int    $line    Line that triggered the error
     *
     * @return bool
     */
    public function handleFatalError($code, $message, $file, $line)
    {
        error_log($message, 0);
        $this->handleException(new FatalErrorException($message, 500, $file, $line));

        return true;
    }

    /**
     * Register handlers
     *
     * @return self
     */
    public function register()
    {
        if (!$this->registered) {
            error_reporting($this->errorLevel);
            set_exception_handler([$this, 'handleException']);
            set_error_handler([$this, 'handleError'], $this->errorLevel);
            register_shutdown_function([$this, 'handleShutdown']);

            $this->registered = true;
        }

        return $this;
    }

    /**
     * Un register handlers
     *
     * @return self
     */
    public function unRegister()
    {
        if ($this->registered) {
            restore_exception_handler();
            restore_error_handler();

            $this->registered = false;
        }

        return $this;
    }
}
