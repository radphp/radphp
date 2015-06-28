<?php

namespace Rad\Core\Exception;

use Exception;

/**
 * Base Exception
 *
 * @package Rad\Core\Exception
 */
class BaseException extends Exception
{
    /**
     * Rad\Core\Exception\BaseException constructor
     *
     * @param string $message  Exception message
     * @param int    $code     Exception code
     * @param null   $previous Previous exception
     */
    public function __construct($message, $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Set file
     *
     * @param string $file
     *
     * @return self
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Set line
     *
     * @param int $line
     *
     * @return self
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }
}
