<?php

namespace Rad\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Network\Http\Response;
use SplPriorityQueue;
use Rad\Core\SingletonTrait;

/**
 * Middleware Collection
 *
 * @package Rad\Routing
 */
class MiddlewareCollection
{
    use SingletonTrait;

    /**
     * @var SplPriorityQueue
     */
    protected static $queue;

    /**
     * Initialize middleware collection
     */
    protected function init()
    {
        self::$queue = new SplPriorityQueue();
    }

    /**
     * Add middleware
     *
     * @param MiddlewareInterface $middleware
     * @param int                 $priority
     *
     * @return self
     */
    public function add(MiddlewareInterface $middleware, $priority = 10)
    {
        self::$queue->insert($middleware, $priority);

        return $this;
    }

    /**
     * Resolve middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function resolve(ServerRequestInterface &$request, ResponseInterface &$response)
    {
        if (false === self::$queue->isEmpty()) {
            self::$queue->top();

            while (self::$queue->valid()) {
                /** @var MiddlewareInterface $middleware */
                $middleware = self::$queue->current();
                $middleware->handle($request, $response);

                $request = !is_null($middleware->getRequest()) ? $middleware->getRequest() : $request;
                $response = !is_null($middleware->getResponse()) ? $middleware->getResponse() : $response;

                self::$queue->next();
            }
        }
    }
}
