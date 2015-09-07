<?php

namespace Rad;

use League\CLImate\Argument\Manager;
use League\CLImate\CLImate;
use Psr\Http\Message\ServerRequestInterface;
use Rad\Configure\Config;
use Rad\Core\Action;
use Rad\Core\Action\MissingMethodException;
use Rad\Core\BundleInterface;
use Rad\Core\Bundles;
use Rad\Core\DotEnv;
use Rad\Core\Exception\BaseException;
use Rad\Core\Responder;
use Rad\Core\SingletonTrait;
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Error\ErrorHandler;
use Rad\Error\Handler\JsonHandler;
use Rad\Events\EventManager;
use Rad\Events\EventSubscriberInterface;
use Rad\Network\Http\Exception\NotFoundException;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Routing\Dispatcher;
use Rad\Routing\Router;

/**
 * RadPHP Application
 *
 * @package Rad
 */
abstract class AbstractApplication
{
    use SingletonTrait;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $bundles = [];

    protected $run = false;

    const EVENT_BEFORE_LOAD_CONFIG = 'App.beforeLoadConfig';
    const EVENT_AFTER_LOAD_CONFIG = 'App.afterLoadConfig';
    const EVENT_BEFORE_LOAD_SERVICE = 'App.beforeLoadService';
    const EVENT_AFTER_LOAD_SERVICE = 'App.afterLoadService';
    const EVENT_BEFORE_BUNDLE_STARTUP = 'App.beforeBundleStartup';
    const EVENT_AFTER_BUNDLE_STARTUP = 'App.afterBundleStartup';

    /**
     * Init application
     *
     * @throws BaseException
     * @throws DependencyInjection\Exception
     */
    protected function init()
    {
        $error = new ErrorHandler();
        $error->setHandler(new JsonHandler())
            ->setDebug(true)
            ->register();

        DotEnv::load(ROOT_DIR);
        if (!getenv('RAD_ENVIRONMENT')) {
            putenv('RAD_ENVIRONMENT=production');
        }

        $this->container = Container::getInstance();

        Config::set('environment', getenv('RAD_ENVIRONMENT'));
        Config::set('debug', (bool)getenv('RAD_DEBUG'));

        Bundles::loadAll($this->registerBundles());
        $this->container->setShared('event_manager', new EventManager(), true);

        $this->loadConfig();
        $this->loadService();
        $this->loadServicesFromConfig();

        $this->container->setShared('router', new Router());

        $this->bundleStartup();
    }

    /**
     * Register Bundles
     *
     * @return BundleInterface[]
     */
    abstract public function registerBundles();

    /**
     * Handle Web Request
     *
     * @param ServerRequestInterface $request
     *
     * @throws BaseException
     * @throws DependencyInjection\Exception\ServiceLockedException
     * @throws DependencyInjection\Exception\ServiceNotFoundException
     * @throws NotFoundException
     * @throws Response\Exception
     */
    public function handleWeb(ServerRequestInterface $request)
    {
        $this->container->set('request', $request);

        /** @var Router $router */
        $router = $this->container->get('router');
        $router->handle();

        $dispatcher = new Dispatcher();
        $response = $dispatcher->setAction($router->getAction())
            ->setActionNamespace($router->getActionNamespace())
            ->setBundle($router->getBundle())
            ->setParams($router->getParams())
            ->setResponderNamespace($router->getResponderNamespace())
            ->setRouteMatched($router->isMatched())
            ->dispatch($request);

        if (!$response instanceof Response) {
            $response = new Response();
        }

        $response->send();
    }

    /**
     * Run application in cli request
     *
     * @param $argv
     *
     * @throws BaseException
     * @throws MissingMethodException
     */
    public function handleCli($argv)
    {
        if (!$this->run) {
            if (!(count($argv) >= 2)) {
                return;
            }

            $route = str_replace(':', '/', $argv[1]);
            unset($argv[0]);

            $this->getRouter()->handle($route);
            $this->callCli(array_values($argv));
            $this->run = true;
        } else {
            throw new BaseException('Application is run.');
        }
    }

    /**
     * Call cli
     *
     * @param array $argv
     *
     * @throws BaseException
     * @throws MissingMethodException
     */
    private function callCli(array $argv)
    {
        if ($this->getRouter()->isMatched()) {
            $cliMethod = 'cliMethod';
            $cliConfig = 'cliConfig';
            $actionNamespace = $this->getRouter()->getActionNamespace();

            if (!is_subclass_of($actionNamespace, 'App\Action\AppAction')) {
                throw new BaseException(
                    sprintf('Action "%s" does not extend App\Action\AppAction', $actionNamespace)
                );
            }

            // Check Action::cliMethod exist or callable
            if (method_exists($actionNamespace, $cliMethod) && is_callable([$actionNamespace, $cliMethod])) {
                $responderNamespace = $this->getRouter()->getResponderNamespace();
                $responder = null;

                if (class_exists($responderNamespace) &&
                    is_subclass_of($responderNamespace, 'App\Responder\AppResponder')
                ) {
                    $responder = new $responderNamespace();
                }

                /** @var ContainerAwareInterface|EventSubscriberInterface $actionInstance */
                $actionInstance = new $actionNamespace($responder);
                $actionInstance->setContainer($this->container);
                $this->getEventManager()->addSubscriber($actionInstance);

                $climate = new CLImate();

                if (method_exists($actionNamespace, $cliConfig) && is_callable([$actionNamespace, $cliConfig])) {
                    $argumentManager = new Manager();
                    $climate->setArgumentManager($argumentManager);

                    $this->getEventManager()->dispatch(Action::EVENT_BEFORE_CLI_CONFIG);
                    call_user_func([$actionInstance, 'cliConfig'], $argumentManager);
                    $this->getEventManager()->dispatch(Action::EVENT_AFTER_CLI_CONFIG);

                    try {
                        $argumentManager->parse($argv);
                    } catch (\Exception $e) {
                        $climate->error($e->getMessage());
                        $climate->usage();
                    }
                }

                $this->getEventManager()->dispatch(Action::EVENT_BEFORE_CLI_METHOD);
                call_user_func([$actionInstance, $cliMethod], $climate);
                $this->getEventManager()->dispatch(Action::EVENT_AFTER_CLI_METHOD);

                // Check Responder::cliMethod exist or callable
                if (method_exists($responder, $cliMethod) && is_callable([$responder, $cliMethod])) {
                    $this->getEventManager()->dispatch(self::EVENT_BEFORE_RESPONDER);
                    call_user_func([$responder, $cliMethod]);
                    $this->getEventManager()->dispatch(self::EVENT_AFTER_RESPONDER);
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
            throw new BaseException(sprintf('Route "%s" does not found', $argv[0]));
        }
    }

    /**
     * Startup bundles
     */
    private function bundleStartup()
    {
        $this->getEventManager()->dispatch(self::EVENT_BEFORE_BUNDLE_STARTUP, $this);

        foreach ($this->registerBundles() as $bundle) {
            $bundle->startup();
        }

        $this->getEventManager()->dispatch(self::EVENT_AFTER_BUNDLE_STARTUP, $this);
    }

    /**
     * Load config from bundles
     *
     * @throws BaseException
     */
    private function loadConfig()
    {
        $appBundle = null;
        $this->getEventManager()->dispatch(self::EVENT_BEFORE_LOAD_CONFIG, $this);

        foreach ($this->registerBundles() as $bundle) {
            if ($bundle instanceof \App\AppBundle) {
                $appBundle = $bundle;
                continue;
            }

            $bundle->loadConfig();
        }

        if (!$appBundle instanceof \App\AppBundle) {
            throw new BaseException('AppBundle does not exist.');
        }

        $appBundle->loadConfig();
        $this->getEventManager()->dispatch(self::EVENT_AFTER_LOAD_CONFIG, $this);
    }

    /**
     * Load services from bundles
     */
    private function loadService()
    {
        $this->getEventManager()->dispatch(self::EVENT_BEFORE_LOAD_SERVICE, $this);

        foreach ($this->registerBundles() as $bundle) {
            $bundle->loadService();
        }

        $this->getEventManager()->dispatch(self::EVENT_AFTER_LOAD_SERVICE, $this);
    }

    /**
     * Load services from config
     *
     * @throws DependencyInjection\Exception\ServiceLockedException
     */
    protected function loadServicesFromConfig()
    {
        foreach (Config::get('services', []) as $name => $service) {
            $service += [
                'shared' => false,
                'locked' => false,
                'definition' => []
            ];

            $this->container->set($name, $service['definition'], $service['shared'], $service['locked']);
        }
    }

    /**
     * Get Router
     *
     * @return Router
     * @throws DependencyInjection\Exception
     */
    public function getRouter()
    {
        return $this->container->get('router');
    }

    /**
     * Get Event Manager
     *
     * @return EventManager
     * @throws DependencyInjection\Exception
     */
    public function getEventManager()
    {
        return $this->container->get('event_manager');
    }
}
