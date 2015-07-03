<?php

namespace Rad\Core;

use Rad\DependencyInjection\ContainerAware;
use Rad\Events\EventManager;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Network\Http\Response\Cookies;
use Rad\Network\Session;
use Rad\Routing\Router;

/**
 * Bundle
 *
 * @method Request         getRequest()      Get Http request
 * @method Response        getResponse()     Get Http response
 * @method Router          getRouter()       Get router
 * @method Cookies         getCookies()      Get cookies
 * @method Session         getSession()      Get cookies
 * @method Responder       getResponder()    Get responder
 * @method EventManager    getEventManager() Get event manager
 *
 * @package Rad\Core
 */
abstract class Bundle extends ContainerAware
{

}
