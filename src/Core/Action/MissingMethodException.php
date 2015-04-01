<?php

namespace Rad\Core\Action;

use Rad\Exception;

/**
 * Missing Method Exception
 *
 * @package Rad\Core\Arch\ADR\Action
 */
class MissingMethodException extends Exception
{
    /**
     * MissingMethodException constructor
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message = "", $code = 500, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
