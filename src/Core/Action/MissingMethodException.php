<?php

namespace Rad\Core\Action;

use Rad\Core\Exception\BaseException;

/**
 * Missing Method Exception
 *
 * @package Rad\Core\Arch\ADR\Action
 */
class MissingMethodException extends BaseException
{
    /**
     * MissingMethodException constructor
     *
     * @param string $message  Exception message
     * @param int    $code     Exception code
     * @param null   $previous Previous exception
     */
    public function __construct($message = '', $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
