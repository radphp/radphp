<?php

namespace Rad\Routing\Tests;

use App\AppBundle;
use Composer\Autoload\ClassLoader;
use PHPUnit_Framework_TestCase;
use Rad\Core\Bundles;
use Rad\DependencyInjection\Container;
use Rad\Network\Http\Request;
use Rad\Routing\Router;
use Test\TestBundle;

/**
 * Config Test
 *
 * @package Rad\Configure\Tests
 */
class EtcTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Router
     */
    private $router;

    public static function setUpBeforeClass()
    {
        $classLoader = new ClassLoader();
        $classLoader->addPsr4('App\\', __DIR__ . '/Fixtures/src/App');
        $classLoader->addPsr4('Test\\', __DIR__ . '/Fixtures/src/Test');
        $classLoader->register();

        $container = Container::getInstance();
        $container->set('request', new Request());
    }

    protected function setUp()
    {
        $this->router = new Router();
        if (!defined('SRC_DIR')) {
            define('SRC_DIR', __DIR__ . '/Fixtures/src');
        }
        if (!defined('DS')) {
            define('DS', '/');
        }

        $bundles = [
            new AppBundle(),
            new TestBundle()
        ];
        Bundles::loadAll($bundles);
    }

    /**
     * Test bundle and action and params URL handler behaviours
     */
    public function testBundleActionParamsUrlHandler()
    {
        $container = Container::getInstance();
        $container = $container->get('request');
        $this->router->handle('/test/test/param1/param2/');

        $this->assertEquals($this->router->getActionNamespace(), 'Test\Action\TestAction');
        $this->assertEquals($this->router->getResponderNamespace(), 'Test\Responder\TestResponder');
        $this->assertEquals($this->router->getAction(), 'test');
        $this->assertEquals($this->router->getBundle(), 'test');
        $this->assertEquals($this->router->getParams(), ['param1', 'param2']);
        //$this->assertEquals(, ['param1', 'param2']);
    }

    /**
     * Test simple URL generator behaviours
     */
    public function testOtherStuff()
    {
        $this->assertEquals($this->router->getPrefix($this->router->setPrefix(['hello', 'world'])), ['hello', 'world']);
    }
}
