<?php

namespace Rad\Authentication\Exception;

use Exception;
use Rad\Authentication\Exception as AuthenticationException;

/**
 * Credential Invalid Exception
 *
 * @package Rad\Authentication\Exception
 */
class CredentialInvalidException extends AuthenticationException
{
    /**
     * Rad\Authentication\Exception\CredentialInvalidException constructor
     *
     * @param string    $message  The Exception message to throw.
     * @param Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = 'Credential invalid.', Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
