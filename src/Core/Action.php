<?php

namespace Rad\Core;

use Rad\DependencyInjection\Injectable;
use Rad\Network\Http\RequestInterface;
use Rad\Network\Http\Response\CookiesInterface;
use Rad\Network\Http\ResponseInterface;
use Rad\Routing\Router;

/**
 * Action
 *
 * @property RequestInterface  $request
 * @property ResponseInterface $response
 * @property Router            $router
 * @property CookiesInterface  $cookies
 * @property Responder         $responder
 *
 * @package Rad\Core\Arch\ADR
 */
abstract class Action extends Injectable
{
    protected $responder;

    /**
     * Action constructor
     *
     * @param $responder
     */
    public function __construct($responder)
    {
        $this->responder = $responder;
    }
}
