<?php

namespace Rad\Network\Http\Exception;

use Rad\Core\Exception\BaseNetworkException;

/**
 * Forbidden Exception
 *
 * @package Rad\Network\Http\Exception
 */
class Forbidden extends BaseNetworkException
{
    /**
     * Rad\Network\Http\Exception\Forbidden constructor
     *
     * @param string $message  Exception message
     * @param int    $code     Exception code
     * @param null   $previous Previous exception
     */
    public function __construct($message = 'Forbidden', $code = 403, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
