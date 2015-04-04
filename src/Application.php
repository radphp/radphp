<?php

namespace Rad;

use App\Bootstrap;
use Rad\Core\Action\MissingMethodException;
use Rad\Core\DotEnv;
use Rad\Core\Responder;
use Rad\Core\SingletonTrait;
use Rad\DependencyInjection\Di;
use Rad\DependencyInjection\DiInterface;
use Rad\Http\Exception\NotFoundException;
use Rad\Http\Request;
use Rad\Http\RequestInterface;
use Rad\Http\Response;
use Rad\Http\ResponseInterface;
use Rad\Routing\Router;

/**
 * Application
 *
 * @package Rad
 */
class Application
{
    use SingletonTrait;

    /**
     * @var DiInterface
     */
    protected $di;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;
    protected $isRun = false;

    /**
     * Init application
     *
     * @throws Exception
     */
    protected function init()
    {
        DotEnv::load(dirname(dirname(dirname(dirname(__DIR__)))));
        if (!getenv('RAD_ENV')) {
            putenv('RAD_ENV=production');
        }

        $this->di = new Di();
        $this->di->setShared('request', $this->request = new Request());
        $this->di->setShared('response', $this->response = new Response());
        $this->di->setShared('router', $this->router = new Router());
        $this->di->setShared('cookies', new Response\Cookies());

        $appBootstrap = new Bootstrap();
        $appBootstrap->setDi($this->di);
    }

    /**
     * Run application
     *
     * @throws MissingMethodException
     * @throws NotFoundException
     */
    public function run()
    {
        if (!$this->isRun) {
            $this->di->get('router')->handle();
            $this->callAction();
        } else {
            throw new Exception('Application is run.');
        }
    }

    /**
     * Call Action
     *
     * @throws MissingMethodException
     * @throws NotFoundException
     */
    protected function callAction()
    {
        $method = strtolower($this->request->getMethod());
        $actionNamespace = $this->router->getActionNamespace();

        if ($this->router->wasMatched() && is_subclass_of($actionNamespace, 'App\\Action\\AppAction')) {
            if (method_exists($actionNamespace, $method) && is_callable([$actionNamespace, $method])) {
                $responder = $this->callResponder();
                $instance = new $actionNamespace($responder);
                $instance->setDi($this->di);
                call_user_func_array([$instance, $method], $this->router->getParams());

                if (method_exists($responder, $method) && is_callable([$responder, $method])) {
                    call_user_func([$responder, $method]);
                }

                $this->response->send();
            } else {
                throw new MissingMethodException(
                    sprintf(
                        'Method %s::%s() could not be found, or is not accessible.',
                        $actionNamespace,
                        $method
                    )
                );
            }
        } else {
            throw new NotFoundException();
        }
    }

    /**
     * Call Responder
     *
     * @return null|Responder
     */
    protected function callResponder()
    {
        $responderNamespace = $this->router->getResponderNamespace();

        if (class_exists($responderNamespace) && is_subclass_of($responderNamespace, 'App\\Responder\\AppResponder')) {
            return new $responderNamespace($this->request, $this->response);
        } else {
            return null;
        }
    }
}
