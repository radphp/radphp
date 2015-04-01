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
}
