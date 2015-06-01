<?php

namespace Rad;

/**
 * RadPHP Exception
 *
 * @package Rad
 */
class Exception extends \Exception
{
    /**
     * Rad\Exception constructor
     *
     * @param string $message
     * @param int    $code
     * @param null   $previous
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
