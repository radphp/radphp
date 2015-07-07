<?php

namespace Rad\Core;

use Rad\Application;
use Rad\DependencyInjection\ContainerAware;
use Rad\Error\ErrorHandler;
use Rad\Events\Event;
use Rad\Events\EventManager;
use Rad\Events\EventSubscriberInterface;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Network\Http\Response\Cookies;
use Rad\Network\Session;
use Rad\Routing\Router;

/**
 * Action
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
abstract class Action extends ContainerAware implements EventSubscriberInterface
{
    /** @var Responder $responder */
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

    /**
     * Call before web method
     *
     * @param Event $event
     */
    public function beforeWebMethod(Event $event)
    {

    }

    /**
     * Call after web method
     *
     * @param Event $event
     */
    public function afterWebMethod(Event $event)
    {

    }

    /**
     * Call before cli method
     *
     * @param Event $event
     */
    public function beforeCliMethod(Event $event)
    {

    }

    /**
     * Call after cli method
     *
     * @param Event $event
     */
    public function afterCliMethod(Event $event)
    {

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
        $eventManager->attach(Application::EVENT_BEFORE_WEB_METHOD, [$this, 'beforeWebMethod'])
            ->attach(Application::EVENT_AFTER_WEB_METHOD, [$this, 'afterWebMethod'])
            ->attach(Application::EVENT_BEFORE_CLI_METHOD, [$this, 'beforeCLiMethod'])
            ->attach(Application::EVENT_AFTER_CLI_METHOD, [$this, 'afterCLiMethod']);
    }
}
