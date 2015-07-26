<?php

namespace Rad\Routing\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Core\Exception\BaseException;
use Rad\Routing\AbstractMiddleware;
use Rad\Routing\Dispatcher;

/**
 * Dispatcher Middleware
 *
 * @package Rad\Routing\Middleware
 */
class DispatcherMiddleware extends AbstractMiddleware
{
    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response)
    {
        $router = $request->getAttribute('__router__');

        if (is_array($router)) {
            $dispatcher = new Dispatcher();
            $dispatcher->setAction($router['action'])
                ->setActionNamespace($router['action_namespace'])
                ->setBundle($router['bundle'])
                ->setParams($router['params'])
                ->setResponderNamespace($router['responder_namespace'])
                ->setRouteMatched($router['route_matched'])
                ->dispatch($request);
        } else {
            throw new BaseException('You must added "DispatcherMiddleware" after "RouterMiddleware"');
        }

        $this->request = $request;
    }
}
