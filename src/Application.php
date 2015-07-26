<?php

namespace Rad;

use League\CLImate\Argument\Manager;
use League\CLImate\CLImate;
use Rad\Core\Action;
use Rad\Core\Action\MissingMethodException;
use Rad\Core\Bundles;
use Rad\Core\DotEnv;
use Rad\Core\Exception\BaseException;
use Rad\Core\Responder;
use Rad\Core\SingletonTrait;
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\DependencyInjection\Registry;
use Rad\Error\ErrorHandler;
use Rad\Error\Handler\JsonHandler;
use Rad\Events\EventManager;
use Rad\Events\EventSubscriberInterface;
use Rad\Network\Http\Exception\NotFoundException;
use Rad\Network\Http\Request;
use Rad\Network\Http\Response;
use Rad\Network\Session;
use Rad\Routing\MiddlewareCollection;
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

    protected $run = false;

    const EVENT_BEFORE_LOAD_BUNDLES = 'App.beforeLoadBundles';
    const EVENT_AFTER_LOAD_BUNDLES = 'App.afterLoadBundles';
    const EVENT_BEFORE_RESPONDER = 'Action.beforeResponder';
    const EVENT_AFTER_RESPONDER = 'Action.afterResponder';

    /**
     * Init application
     *
     * @throws BaseException
     * @throws DependencyInjection\Exception
     */
    protected function init()
    {
        $error = (new ErrorHandler())
            ->setHandler(new JsonHandler())
            ->setDebug(true)
            ->register();

        DotEnv::load(ROOT_DIR);
        if (!getenv('RAD_ENV')) {
            putenv('RAD_ENV=production');
        }

        $this->container = Container::getInstance();

        $this->container->setShared('error_handler', $error, true);
        $this->container->setShared('registry', Registry::getInstance(), true);
        $this->container->setShared('router', new Router());
        $this->container->setShared('event_manager', new EventManager(), true);
        $this->container->setShared(
            'session',
            function () {
                $session = new Session();
                $session->start();

                return $session;
            },
            true
        );

        $this->loadConfig();

        $this->getEventManager()->dispatch(self::EVENT_BEFORE_LOAD_BUNDLES);
        $this->loadBundles();
        $this->getEventManager()->dispatch(self::EVENT_AFTER_LOAD_BUNDLES);
    }

    /**
     * Run application in web request
     *
     * @param $request
     * @param $response
     *
     * @throws BaseException
     * @throws DependencyInjection\Exception
     * @throws MissingMethodException
     * @throws NotFoundException
     */
    public function runWeb($request, $response)
    {
        if (!$this->run) {
            $this->container->setShared('request', $request);
            $this->container->setShared('response', $response);
            $this->container->setShared('cookies', new Response\Cookies(), true);

            MiddlewareCollection::getInstance()->resolve($request, $response);
            $response->send();

            $this->run = true;
        } else {
            throw new BaseException('Application is run.');
        }
    }

    /**
     * Run application in cli request
     *
     * @throws BaseException
     */
    public function runCli()
    {
        if (!$this->run) {
            $argv = $_SERVER['argv'];

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
     * Load bundles
     */
    protected function loadBundles()
    {
        foreach (Config::get('bundles', []) as $bundleName => $options) {
            Bundles::load($bundleName, $options);

            $bundleBootstrap = Bundles::getNamespace($bundleName) . 'Bootstrap';

            // check if Bootstrap file is there!
            if (class_exists($bundleBootstrap)) {
                (new $bundleBootstrap())->startup();
            }
        }
    }

    /**
     * Load config
     */
    protected function loadConfig()
    {
        Config::load(CONFIG_DIR . DS . 'config.default.php');
        Config::load(CONFIG_DIR . DS . sprintf('config.%s.php', getenv('RAD_ENV')));
        Config::set('env', getenv('RAD_ENV'));
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
     * Get Request
     *
     * @return Request
     * @throws DependencyInjection\Exception
     */
    public function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * Get Response
     *
     * @return Response
     * @throws DependencyInjection\Exception
     */
    public function getResponse()
    {
        return $this->container->get('response');
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
