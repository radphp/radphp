<?php

namespace Rad\Routing;

use Rad\Core\Action;
use Rad\Core\Responder;
use Rad\Network\Http\Response;
use Rad\Events\EventManagerTrait;
use Rad\Core\Exception\BaseException;
use Rad\Core\Action\MissingMethodException;
use Psr\Http\Message\ServerRequestInterface;
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

    const EVENT_BEFORE_DISPATCH = 'Dispatcher.beforeDispatch';
    const EVENT_AFTER_DISPATCH = 'Dispatcher.afterDispatch';

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
     * @return Response
     * @throws BaseException
     * @throws MissingMethodException
     * @throws NotFoundException
     */
    public function dispatch(ServerRequestInterface $request)
    {
        if ($this->routeMatched) {
            if (!is_subclass_of($this->actionNamespace, 'App\Action\AppAction')) {
                throw new BaseException(
                    sprintf('Action "%s" does not extend App\Action\AppAction', $this->actionNamespace)
                );
            }

            $actionInstance = new $this->actionNamespace();

            $beforeDispatchEvent = $this->dispatchEvent(
                self::EVENT_BEFORE_DISPATCH,
                $this,
                ['request' => $request, 'action' => $actionInstance]
            );

            if ($beforeDispatchEvent->getResult() instanceof Response) {
                return $beforeDispatchEvent->getResult();
            }

            $response = call_user_func_array([$actionInstance, 'invoker'], $this->params);
            $this->dispatchEvent(self::EVENT_AFTER_DISPATCH, $this, ['request' => $request, 'response' => $response]);

            return $response;
        } else {
            throw new NotFoundException(
                sprintf('Route "%s" does not found', $request->getServerParams()['REQUEST_URI'])
            );
        }
    }
}
