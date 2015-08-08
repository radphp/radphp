<?php

namespace Rad\Core;

use Rad\Core\Exception\BaseException;
use Rad\Events\EventManager;
use Rad\Events\EventManagerTrait;
use Rad\Events\EventSubscriberInterface;
use Rad\DependencyInjection\ContainerAware;
use Rad\Network\Http\Request;
use Rad\Network\Http\RequestStacker;
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
    use EventManagerTrait;

    protected $data = [];

    const EVENT_BEFORE_CALL_METHOD = 'Responder.beforeCallMethod';
    const EVENT_AFTER_CALL_METHOD = 'Responder.afterCallMethod';

    /**
     * Invoke responder
     *
     * @return mixed
     * @throws BaseException
     */
    public function __invoke()
    {
        $method = strtolower($this->getRequest()->getMethod()) . 'Method';

        if (method_exists($this, $method) && is_callable([$this, $method])) {
            $beforeCallEvent = $this->dispatchEvent(
                self::EVENT_BEFORE_CALL_METHOD,
                $this,
                ['request' => $this->getRequest()]
            );

            if ($beforeCallEvent->getResult() instanceof Response) {
                return $beforeCallEvent->getResult();
            }

            $response = call_user_func([$this, $method]);
            $this->dispatchEvent(
                self::EVENT_AFTER_CALL_METHOD,
                $this,
                ['request' => $this->getRequest(), 'response' => $response]
            );

            return $response;
        } else {
            throw new BaseException(
                sprintf(
                    'Method %s::%s() could not be found, or is not accessible.',
                    get_class($this),
                    $method
                )
            );
        }
    }

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

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Response
     */
    protected function setContent($content)
    {
        return new Response($content);
    }
}
