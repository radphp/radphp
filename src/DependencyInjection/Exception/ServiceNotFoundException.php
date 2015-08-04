<?php

namespace Rad\DependencyInjection\Exception;

use Exception;
use Rad\DependencyInjection\Exception as DependencyInjectionException;

/**
 * Service Not Found Exception
 *
 * @package Rad\DependencyInjection\Exception
 */
class ServiceNotFoundException extends DependencyInjectionException
{
    /**
     * Rad\DependencyInjection\Exception\ServiceNotFoundException constructor
     *
     * @param string    $message  Exception message
     * @param Exception $previous Previous exception
     */
    public function __construct($message = 'Service not found.', Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
