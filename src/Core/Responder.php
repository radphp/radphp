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
     * @param string $name    Data name
     * @param mixed  $default Default value to return
     *
     * @return mixed
     */
    public function getData($name, $default = null)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return $default;
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

    protected function setRawContent($content)
    {
        $this->getResponse()->setContent($content);
    }

    protected function setContent($template, $params)
    {
        if ($this->getRequest()->isAjax()) {
            $content = json_encode($params);
        } else {
            /** @var \Twig_Environment $twig */
            $twig = $this->getContainer()->get('twig');
            $content = $twig->render($template, $params);
        }

        $this->getResponse()->setContent($content);
    }
}
