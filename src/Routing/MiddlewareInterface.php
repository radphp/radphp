<?php

namespace Rad\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware Interface
 *
 * @package Rad\Routing
 */
interface MiddlewareInterface
{
    /**
     * Handle middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return void
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response);
}
