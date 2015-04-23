<?php

namespace Rad;

use App\Bootstrap;
use League\CLImate\Argument\Manager;
use League\CLImate\CLImate;
use Rad\Core\Action\MissingMethodException;
use Rad\Core\Bundles;
use Rad\Core\DotEnv;
use Rad\Core\Responder;
use Rad\Core\SingletonTrait;
use Rad\DependencyInjection\Di;
use Rad\DependencyInjection\DiInterface;
use Rad\Network\Http\Exception\NotFoundException;
use Rad\Network\Http\Request;
use Rad\Network\Http\RequestInterface;
use Rad\Network\Http\Response;
use Rad\Network\Http\ResponseInterface;
use Rad\Network\Session;
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
    protected $run = false;

    /**
     * Init application
     *
     * @throws Exception
     */
    protected function init()
    {
        DotEnv::load(ROOT);
        if (!getenv('RAD_ENV')) {
            putenv('RAD_ENV=production');
        }

        $this->di = new Di();
        $this->di->setShared('router', $this->router = new Router());
        $this->di->setShared(
            'session',
            function () {
                $session = new Session();
                $session->start();

                return $session;
            }
        );

        $appBootstrap = new Bootstrap();
        $appBootstrap->setDi($this->di);

        $this->loadBundles();
    }

    /**
     * Run application in web request
     *
     * @throws MissingMethodException
     * @throws NotFoundException
     */
    public function runWeb()
    {
        if (!$this->run) {
            $this->di->setShared('request', $this->request = new Request());
            $this->di->setShared('response', $this->response = new Response());
            $this->di->setShared('cookies', new Response\Cookies());

            $this->router->handle();
            $this->callAction();
            $this->run = true;
        } else {
            throw new Exception('Application is run.');
        }
    }

    /**
     * Run application in cli request
     *
     * @throws Exception
     * @throws MissingMethodException
     */
    public function runCli()
    {
        if (!$this->run) {
            global $argv;

            if (!(count($argv) >= 2)) {
                return;
            }

            $route = str_replace(':', '/', $argv[1]);
            unset($argv[0]);

            $this->router->handle($route);
            $this->callCli(array_values($argv));
            $this->run = true;
        } else {
            throw new Exception('Application is run.');
        }
    }

    /**
     * Call cli
     *
     * @param array $argv
     *
     * @throws Exception
     * @throws MissingMethodException
     */
    protected function callCli(array $argv)
    {
        if ($this->router->wasMatched()) {
            $cliMethod = 'cliMethod';
            $cliConfig = 'cliConfig';
            $actionNamespace = $this->router->getActionNamespace();

            if (!is_subclass_of($actionNamespace, 'App\\Action\\AppAction')) {
                throw new Exception(sprintf('Action "%s" does not extend App\\Action\\AppAction', $actionNamespace));
            }

            // Check Action::cliMethod exist or callable
            if (method_exists($actionNamespace, $cliMethod) && is_callable([$actionNamespace, $cliMethod])) {
                $responder = $this->callResponder();
                $instance = new $actionNamespace($responder);
                $instance->setDi($this->di);

                $climate = new CLImate();

                if (!(method_exists($actionNamespace, $cliConfig) || is_callable([$actionNamespace, $cliConfig]))) {
                    $argumentManager = new Manager();
                    $climate->setArgumentManager($argumentManager);

                    call_user_func([$instance, 'cliConfig'], $argumentManager);

                    try {
                        $argumentManager->parse($argv);
                    } catch (\Exception $e) {
                        $climate->error($e->getMessage());
                        $climate->usage();
                    }
                }

                call_user_func([$instance, $cliMethod], $climate);

                // Check Responder::cliMethod exist or callable
                if (method_exists($responder, $cliMethod) && is_callable([$responder, $cliMethod])) {
                    call_user_func([$responder, $cliMethod]);
                }
            } else {
                throw new MissingMethodException(
                    sprintf(
                        'Method %s::%s() could not be found, or is not accessible.',
                        $actionNamespace,
                        $cliMethod
                    )
                );
            }
        } else {
            throw new Exception(sprintf('Route "%s" does not found', $argv[0]));
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
        if ($this->router->wasMatched()) {
            $method = strtolower($this->request->getMethod()) . 'Method';
            $actionNamespace = $this->router->getActionNamespace();

            if (!is_subclass_of($actionNamespace, 'App\\Action\\AppAction')) {
                throw new Exception(sprintf('Action "%s" does not extend App\\Action\\AppAction', $actionNamespace));
            }

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
            throw new NotFoundException(
                sprintf(
                    'Route "%s" does not found',
                    $this->request->getQuery('_url', $this->request->getServer('REQUEST_URI'), true)
                )
            );
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

    /**
     * Load bundles
     *
     * @throws Exception
     */
    protected function loadBundles()
    {
        foreach (Config::get('bundles', []) as $bundleName => $namespace) {
            Bundles::load(
                $bundleName,
                Config::get('bundles.' . $bundleName . '.namespace'),
                Config::get('bundles.' . $bundleName . '.options')
            );
        }
    }
}
