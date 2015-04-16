<?php

namespace Rad\Network\Http\Exception;

use Rad\Exception;

/**
 * NotFoundException
 *
 * @package Rad\Network\Http\Exception
 */
class NotFoundException extends Exception
{
    /**
     * NotFoundException constructor
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous \
     */
    public function __construct($message = '', $code = 404, \Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'Not Found';
        }

        parent::__construct($message, $code, $previous);
    }
}
