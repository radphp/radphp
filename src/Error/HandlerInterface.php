<?php

namespace Rad\Error;

use Exception;

/**
 * Handler Interface
 *
 * @package Rad\Error
 */
interface HandlerInterface
{
    /**
     * Handle exception
     *
     * @param Exception $exception
     *
     * @return string
     */
    public function handle(Exception $exception);
}
