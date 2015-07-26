<?php

namespace Rad\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\DependencyInjection\ContainerAwareTrait;

/**
 * Abstract Middleware
 *
 * @package Rad\Routing
 */
abstract class AbstractMiddleware implements MiddlewareInterface
{
    use ContainerAwareTrait;

    protected $request;
    protected $response;

    /**
     * {@inheritdoc}
     */
    abstract public function handle(ServerRequestInterface $request, ResponseInterface $response);

    /**
     * Get request
     *
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get response
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
