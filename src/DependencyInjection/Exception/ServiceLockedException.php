<?php

namespace Rad\DependencyInjection\Exception;

use Exception;
use Rad\DependencyInjection\Exception as DependencyInjectionException;

/**
 * Service Locked Exception
 *
 * @package Rad\DependencyInjection\Exception
 */
class ServiceLockedException extends DependencyInjectionException
{
    /**
     * Rad\DependencyInjection\Exception\ServiceLockedException constructor
     *
     * @param string    $message  Exception message
     * @param Exception $previous Previous exception
     */
    public function __construct($message = 'Service is locked.', Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
