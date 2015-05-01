<?php

namespace Rad\Core;

use Rad\DependencyInjection\ContainerAware;
use Rad\Error\ErrorHandler;
use Rad\Event\EventDispatcher;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Network\Http\Response\Cookies;
use Rad\Network\Session;
use Rad\Routing\Router;

/**
 * Action
 *
 * @property Request   $request
 * @property Response  $response
 * @property Router    $router
 * @property Cookies   $cookies
 * @property Session   $session
 * @property Responder $responder
 *
 * @method Request         getRequest()  Get Http request
 * @method Response        getResponse() Get Http response
 * @method Router          getRouter()   Get router
 * @method Cookies         getCookies()  Get cookies
 * @method EventDispatcher getEvent()    Get event dispatcher
 * @method ErrorHandler    getError()    Get error handler
 *
 * @package Rad\Core
 */
abstract class Action extends ContainerAware
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
