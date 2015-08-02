<?php

namespace Rad\Authentication\Exception;

use Exception;
use Rad\Authentication\Exception as AuthenticationException;

/**
 * Identity Not Found Exception
 *
 * @package Rad\Authentication\Exception
 */
class IdentityNotFoundException extends AuthenticationException
{
    /**
     * Rad\Authentication\Exception\IdentityNotFoundException constructor
     *
     * @param string    $message  The Exception message to throw.
     * @param Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = 'Identity not found.', Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
