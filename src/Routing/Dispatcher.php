<?php

namespace Rad\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Rad\Core\Action;
use Rad\Core\Action\MissingMethodException;
use Rad\Core\Exception\BaseException;
use Rad\Core\Responder;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Events\EventManagerTrait;
use Rad\Events\EventSubscriberInterface;
use Rad\Network\Http\Exception\NotFoundException;

/**
 * Dispatcher
 *
 * @package Rad\Routing
 */
class Dispatcher
{
    use EventManagerTrait;

    protected $bundle;
    protected $action;
    protected $actionNamespace;
    protected $responderNamespace;
    protected $params = [];
    protected $routeMatched = false;

    /**
     * Set bundle
     *
     * @param string $bundle Bundle name
     *
     * @return self
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    /**
     * Get bundle name
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set action namespace
     *
     * @param string $actionNamespace
     *
     * @return self
     */
    public function setActionNamespace($actionNamespace)
    {
        $this->actionNamespace = $actionNamespace;

        return $this;
    }

    /**
     * Get action namespace
     *
     * @return string
     */
    public function getActionNamespace()
    {
        return $this->actionNamespace;
    }

    /**
     * Set responder namespace
     *
     * @param string $responderNamespace
     *
     * @return self
     */
    public function setResponderNamespace($responderNamespace)
    {
        $this->responderNamespace = $responderNamespace;

        return $this;
    }

    /**
     * Get responder namespace
     *
     * @return string
     */
    public function getResponderNamespace()
    {
        return $this->responderNamespace;
    }

    /**
     * Set params
     *
     * @param array $params
     *
     * @return self
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set route matched
     *
     * @param boolean $routeMatched
     *
     * @return self
     */
    public function setRouteMatched($routeMatched)
    {
        $this->routeMatched = (bool)$routeMatched;

        return $this;
    }

    /**
     * Is route matched
     *
     * @return boolean
     */
    public function isRouteMatched()
    {
        return $this->routeMatched;
    }

    /**
     * Dispatch
     *
     * @param ServerRequestInterface $request
     *
     * @throws BaseException
     * @throws MissingMethodException
     * @throws NotFoundException
     */
    public function dispatch(ServerRequestInterface $request)
    {
        if ($this->routeMatched) {
            $method = strtolower($request->getMethod()) . 'Method';

            if (!is_subclass_of($this->actionNamespace, 'App\Action\AppAction')) {
                throw new BaseException(
                    sprintf('Action "%s" does not extend App\Action\AppAction', $this->actionNamespace)
                );
            }

            $responder = null;
            if (class_exists($this->responderNamespace) &&
                is_subclass_of($this->responderNamespace, 'App\Responder\AppResponder')
            ) {
                $responder = new $this->responderNamespace();
            }

            /** @var Callable|ContainerAwareInterface|EventSubscriberInterface $actionInstance */
            $actionInstance = new $this->actionNamespace($responder);

            if (method_exists($actionInstance, $method) && is_callable([$actionInstance, $method])) {
                $invokeAction = [$actionInstance, $method];
                $invokeResponder = [$responder, $method];
            } elseif (is_callable($actionInstance)) {
                $invokeAction = $actionInstance;
                $invokeResponder = $responder;
            } else {
                throw new MissingMethodException(
                    sprintf(
                        'Method %s::%s() could not be found, or is not accessible.',
                        $this->actionNamespace,
                        $method
                    )
                );
            }

            if ($actionInstance instanceof EventSubscriberInterface) {
                $this->getEventManager()->addSubscriber($actionInstance);
            }

            $this->dispatchEvent(Action::EVENT_BEFORE_WEB_METHOD);
            call_user_func_array($invokeAction, $this->params);
            $this->dispatchEvent(Action::EVENT_AFTER_WEB_METHOD);

            if ((method_exists($responder, $method) && is_callable([$responder, $method]))
                || is_callable($invokeResponder)
            ) {
                $this->dispatchEvent('Action.beforeResponder');
                call_user_func($invokeResponder);
                $this->dispatchEvent('Action.afterResponder');
            }
        } else {
            throw new NotFoundException(
                sprintf('Route "%s" does not found', $request->getServerParams()['REQUEST_URI'])
            );
        }
    }
}
