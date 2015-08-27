<?php

namespace Rad\Core;

use Rad\Core\Action\MissingMethodException;
use Rad\Core\Exception\BaseException;
use Rad\DependencyInjection\ContainerAware;
use Rad\Events\Event;
use Rad\Events\EventManager;
use Rad\Events\EventManagerTrait;
use Rad\Events\EventSubscriberInterface;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Network\Http\Response\Cookies;
use Rad\Network\Session;
use Rad\Network\Session\Flash\FlashBag;
use Rad\Routing\Dispatcher;
use Rad\Routing\Router;
use ReflectionMethod;

/**
 * Action
 *
 * @method Request         getRequest()      Get Http request
 * @method Response        getResponse()     Get Http response
 * @method Router          getRouter()       Get router
 * @method Cookies         getCookies()      Get cookies
 * @method Session         getSession()      Get cookies
 * @method EventManager    getEventManager() Get event manager
 * @method FlashBag        getFlash()        Get flash bag
 *
 * @package Rad\Core
 */
abstract class Action extends ContainerAware implements EventSubscriberInterface
{
    use EventManagerTrait;

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
     * Default action invoke magic method
     *
     * @return mixed|null
     * @throws BaseException
     * @throws MissingMethodException
     */
    public function __invoke()
    {
        $method = strtolower($this->getRequest()->getMethod()) . 'Method';

        if (method_exists($this, $method) && is_callable([$this, $method])) {
            call_user_func_array([$this, $method], func_get_args());
        } else {
            throw new MissingMethodException(
                sprintf(
                    'Method %s::%s() could not be found, or is not accessible.',
                    get_class($this),
                    $method
                )
            );
        }
    }

    /**
     * Invoke action
     *
     * @return mixed|null
     * @throws BaseException
     * @throws MissingMethodException
     */
    final public function invoker()
    {
        $this->getEventManager()->addSubscriber($this);

        $this->dispatchEvent(Action::EVENT_BEFORE_WEB_METHOD, $this, ['request' => $this->getRequest()]);
        $response = call_user_func_array($this, func_get_args());
        $this->dispatchEvent(
            Action::EVENT_AFTER_WEB_METHOD,
            $this,
            ['request' => $this->getRequest(), 'response' => $response]
        );

        if ($response instanceof Response) {
            return $response;
        }

        if ($this->getResponder()) {
            $response = call_user_func_array([$this->getResponder(), 'invoker'], func_get_args());

            if (!$response instanceof Response) {
                throw new BaseException(
                    sprintf('Responder "%s" must be return Response object.', get_class($this->getResponder()))
                );
            }

            return $response;
        }
    }

    /**
     * Get responder
     *
     * @return null|Responder
     * @throws BaseException
     * @throws \Rad\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function getResponder()
    {
        if (null !== $this->responder) {
            return $this->responder;
        }

        /** @var Router $router */
        $router = $this->getContainer()->get('router');
        $namespace = $router->getResponderNamespace();

        if (class_exists($namespace)) {
            if (!is_subclass_of($namespace, 'App\Responder\AppResponder')) {
                throw new BaseException(
                    sprintf('Your "%s" responder must be extended "App\Responder\AppResponder".', $namespace));
            }

            $this->responder = new $namespace();
        }

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
     * @return Response
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

        $oldRequestUri = $_SERVER['REQUEST_URI'];
        $oldGetUrl = $_GET['_url'];
        $_GET['_url'] = $_SERVER['REQUEST_URI'] = $uri;
        $this->getContainer()->setShared('router', new Router());
        $this->getRouter()->setPrefix($oldRouter->getPrefix());
        $this->getRouter()->handle($uri);

        $dispatcher = new Dispatcher();
        $response = $dispatcher->setAction($this->getRouter()->getAction())
            ->setActionNamespace($this->getRouter()->getActionNamespace())
            ->setBundle($this->getRouter()->getBundle())
            ->setParams($this->getRouter()->getParams())
            ->setResponderNamespace($this->getRouter()->getResponderNamespace())
            ->setRouteMatched($this->getRouter()->isMatched())
            ->dispatch($request);

        // restore latest state
        $this->getContainer()->setShared('router', $oldRouter);
        $_SERVER['REQUEST_URI'] = $oldRequestUri;
        $_GET['_url'] = $oldGetUrl;

        return $response;
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
