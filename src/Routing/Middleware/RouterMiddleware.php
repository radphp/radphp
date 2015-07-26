<?php

namespace Rad\Routing\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Routing\AbstractMiddleware;
use Rad\Routing\Router;

/**
 * Router Middleware
 *
 * @package Rad\Routing\Middleware
 */
class RouterMiddleware extends AbstractMiddleware
{
    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response)
    {
        /** @var Router $router */
        $router = $this->getContainer()->get('router');

        $router->handle();
        $this->request = $request->withAttribute(
            '__router__',
            [
                'bundle' => $router->getBundle(),
                'action' => $router->getAction(),
                'action_namespace' => $router->getActionNamespace(),
                'responder_namespace' => $router->getResponderNamespace(),
                'params' => $router->getParams(),
                'route_matched' => $router->isMatched(),
            ]
        );
    }
}
