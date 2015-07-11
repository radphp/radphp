<?php

namespace Rad\Core;

use Rad\Events\EventManager;
use Rad\Events\EventSubscriberInterface;
use Rad\DependencyInjection\ContainerAware;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Network\Http\Response\Cookies;
use Rad\Network\Session;
use Rad\Routing\Router;

/**
 * Responder
 * @method Request         getRequest()      Get Http request
 * @method Response        getResponse()     Get Http response
 * @method Router          getRouter()       Get router
 * @method Cookies         getCookies()      Get cookies
 * @method Session         getSession()      Get cookies
 * @method EventManager    getEventManager() Get event manager
 *
 * @package Rad\Core
 */
abstract class Responder extends ContainerAware implements EventSubscriberInterface
{
    protected $data = [];

    /**
     * Set data
     *
     * @param string $name  Data name
     * @param mixed  $value Data value
     */
    public function setData($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Get data
     *
     * @param string $name Data name
     *
     * @return mixed
     */
    public function getData($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * Subscribe event listener
     *
     * @param EventManager $eventManager
     *
     * @return mixed
     */
    public function subscribe(EventManager $eventManager)
    {

    }
}
