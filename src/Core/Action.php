<?php

namespace Rad\Core;

use Rad\Core\Exception\BaseException;
use Rad\DependencyInjection\ContainerAware;
use Rad\Events\Event;
use Rad\Events\EventManager;
use Rad\Events\EventSubscriberInterface;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Network\Http\Response\Cookies;
use Rad\Network\Session;
use Rad\Routing\Dispatcher;
use Rad\Routing\Router;

/**
 * Action
 *
 * @method Request         getRequest()      Get Http request
 * @method Response        getResponse()     Get Http response
 * @method Router          getRouter()       Get router
 * @method Cookies         getCookies()      Get cookies
 * @method Session         getSession()      Get cookies
 * @method EventManager    getEventManager() Get event manager
 *
 * @package Rad\Core
 */
abstract class Action extends ContainerAware implements EventSubscriberInterface
{
    /**
     * @var Responder
     */
    protected $responder;

    const EVENT_BEFORE_WEB_METHOD = 'Action.beforeWebMethod';
    const EVENT_AFTER_WEB_METHOD = 'Action.afterWebMethod';
    const EVENT_BEFORE_CLI_METHOD = 'Action.beforeCliMethod';
    const EVENT_AFTER_CLI_METHOD = 'Action.afterCliMethod';
    const EVENT_BEFORE_CLI_CONFIG = 'Action.beforeCliConfig';
    const EVENT_AFTER_CLI_CONFIG = 'Action.afterCliConfig';

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
     * Get responder
     *
     * @return Responder
     */
    public function getResponder()
    {
        return $this->responder;
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
     * Forward to other action without redirect
     *
     * @param string $uri
     * @param string $method
     *
     * @throws BaseException
     */
    public function forward($uri, $method = Request::METHOD_GET)
    {
        if ('cli' === PHP_SAPI) {
            throw new BaseException('You can not call forward method in cli mode.');
        }

        $request = $this->getRequest()->withMethod($method);

        $this->getContainer()->setShared('request', $request);

        $oldRouter = $this->getRouter();

        $this->getContainer()->setShared('router', new Router());
        $this->getRouter()->handle($uri);

        $dispatcher = new Dispatcher();
        $dispatcher->setAction($this->getRouter()->getAction())
            ->setActionNamespace($this->getRouter()->getActionNamespace())
            ->setBundle($this->getRouter()->getBundle())
            ->setParams($this->getRouter()->getParams())
            ->setResponderNamespace($this->getRouter()->getResponderNamespace())
            ->setRouteMatched($this->getRouter()->isMatched())
            ->dispatch($request);

        $this->getContainer()->setShared('router', $oldRouter);
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
        $eventManager->attach(self::EVENT_BEFORE_WEB_METHOD, [$this, 'beforeWebMethod'])
            ->attach(self::EVENT_AFTER_WEB_METHOD, [$this, 'afterWebMethod'])
            ->attach(self::EVENT_BEFORE_CLI_METHOD, [$this, 'beforeCLiMethod'])
            ->attach(self::EVENT_AFTER_CLI_METHOD, [$this, 'afterCLiMethod']);
    }
}
