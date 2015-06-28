<?php

namespace Rad\Network\Http\Exception;

use Rad\Core\Exception\BaseNetworkException;

/**
 * NotFoundException
 *
 * @package Rad\Network\Http\Exception
 */
class NotFoundException extends BaseNetworkException
{
    /**
     * NotFoundException constructor
     *
     * @param string $message  Exception message
     * @param int    $code     Exception code
     * @param null   $previous Previous exception
     */
    public function __construct($message = 'Not Found', $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
