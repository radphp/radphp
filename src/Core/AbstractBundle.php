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
abstract class AbstractBundle extends ContainerAware implements BundleInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    public function startup()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function loadService()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->name = substr(end(explode('\\', get_class($this))), 0, -6);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        if ($this->namespace) {
            return $this->namespace;
        }

        return $this->namespace = rtrim(str_replace($this->getName() . 'Bundle', '', get_class($this)), '\\') . '\\';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        return $this->path = SRC_DIR . DS . $this->getName();
    }
}
