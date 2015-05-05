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
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Event\EventDispatcher;
use Rad\Network\Http\Exception\NotFoundException;
use Rad\Network\Http\Request;
use Rad\Network\Http\RequestInterface;
use Rad\Network\Http\Response;
use Rad\Network\Http\ResponseInterface;
use Rad\Network\Session;
use Rad\Routing\Router;

/**
 * RadPHP Application
 *
 * @package Rad
 */
class Application
{
    use SingletonTrait;

    /**
     * @var Container
     */
    protected $container;

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

    /**
     * @var EventDispatcher
     */
    protected $event;

    protected $run = false;

    const EVENT_BEFORE_LOAD_BUNDLES = 'App.beforeLoadBundles';
    const EVENT_AFTER_LOAD_BUNDLES = 'App.afterLoadBundles';
    const EVENT_BEFORE_WEB_METHOD = 'Action.beforeWebMethod';
    const EVENT_AFTER_WEB_METHOD = 'Action.afterWebMethod';
    const EVENT_BEFORE_RESPONDER = 'Action.beforeResponder';
    const EVENT_AFTER_RESPONDER = 'Action.afterResponder';
    const EVENT_BEFORE_CLI_METHOD = 'Action.beforeCliMethod';
    const EVENT_AFTER_CLI_METHOD = 'Action.afterCliMethod';
    const EVENT_BEFORE_CLI_CONFIG = 'Action.beforeCliConfig';
    const EVENT_AFTER_CLI_CONFIG = 'Action.afterCliConfig';

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

        $this->container = new Container();

        $this->container->setShared('router', $this->router = new Router(), true);
        $this->container->setShared('event', $this->event = new EventDispatcher(), true);
        $this->container->setShared(
            'session',
            function () {
                $session = new Session();
                $session->start();

                return $session;
            },
            true
        );

        $appBootstrap = new Bootstrap();
        $appBootstrap->setContainer($this->container);

        $this->event->trigger(self::EVENT_BEFORE_LOAD_BUNDLES, $this->container);
        $this->loadBundles();
        $this->event->trigger(self::EVENT_AFTER_LOAD_BUNDLES, $this->container);
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
            $this->container->setShared('request', $this->request = new Request(), true);
            $this->container->setShared('response', $this->response = new Response(), true);
            $this->container->setShared('cookies', new Response\Cookies(), true);

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
    private function callCli(array $argv)
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
                /** @var ContainerAwareInterface $actionInstance */
                $actionInstance = new $actionNamespace($responder);
                $actionInstance->setContainer($this->container);

                $climate = new CLImate();

                if (method_exists($actionNamespace, $cliConfig) && is_callable([$actionNamespace, $cliConfig])) {
                    $argumentManager = new Manager();
                    $climate->setArgumentManager($argumentManager);

                    $this->event->trigger(self::EVENT_BEFORE_CLI_CONFIG, $this->container);
                    call_user_func([$actionInstance, 'cliConfig'], $argumentManager);
                    $this->event->trigger(self::EVENT_AFTER_CLI_CONFIG, $this->container);

                    try {
                        $argumentManager->parse($argv);
                    } catch (\Exception $e) {
                        $climate->error($e->getMessage());
                        $climate->usage();
                    }
                }

                $this->event->trigger(self::EVENT_BEFORE_CLI_METHOD, $this->container);
                call_user_func([$actionInstance, $cliMethod], $climate);
                $this->event->trigger(self::EVENT_AFTER_CLI_METHOD, $this->container);

                // Check Responder::cliMethod exist or callable
                if (method_exists($responder, $cliMethod) && is_callable([$responder, $cliMethod])) {
                    $this->event->trigger(self::EVENT_BEFORE_RESPONDER, $this->container);
                    call_user_func([$responder, $cliMethod]);
                    $this->event->trigger(self::EVENT_AFTER_RESPONDER, $this->container);
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
                /** @var ContainerAwareInterface $actionInstance */
                $actionInstance = new $actionNamespace($responder);
                $actionInstance->setContainer($this->container);

                $this->event->trigger(self::EVENT_BEFORE_WEB_METHOD, $this->container);
                call_user_func_array([$actionInstance, $method], $this->router->getParams());
                $this->event->trigger(self::EVENT_AFTER_WEB_METHOD, $this->container);

                if (method_exists($responder, $method) && is_callable([$responder, $method])) {
                    $this->event->trigger(self::EVENT_BEFORE_RESPONDER, $this->container);
                    call_user_func([$responder, $method]);
                    $this->event->trigger(self::EVENT_AFTER_RESPONDER, $this->container);
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
