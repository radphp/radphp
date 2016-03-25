<?php

namespace Rad\Routing\Tests;

use App\AppBundle;
use Composer\Autoload\ClassLoader;
use PHPUnit_Framework_TestCase;
use Rad\Core\Bundles;
use Rad\Routing\Router;
use Test\TestBundle;

/**
 * Config Test
 *
 * @package Rad\Configure\Tests
 */
class RoutingTest extends PHPUnit_Framework_TestCase
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
     * Test root URL handler behaviours
     */
    public function testRootUrlHandler()
    {
        $this->router->handle('/');

        $this->assertEquals($this->router->getActionNamespace(), 'App\Action\IndexAction');
        $this->assertEquals($this->router->getResponderNamespace(), 'App\Responder\IndexResponder');
        $this->assertEquals($this->router->getAction(), 'index');
        $this->assertEquals($this->router->getBundle(), 'app');
        //$this->assertEquals($this->router->getParams(), []);
    }

    /**
     * Test bundle only URL handler behaviours
     */
    public function testBundleUrlHandler()
    {
        $this->router->handle('/test/');

        $this->assertEquals($this->router->getActionNamespace(), 'Test\Action\IndexAction');
        $this->assertEquals($this->router->getResponderNamespace(), 'Test\Responder\IndexResponder');
        $this->assertEquals($this->router->getAction(), 'index');
        $this->assertEquals($this->router->getBundle(), 'test');
        //$this->assertEquals($this->router->getParams(), []);
    }

    /**
     * Test bundle with method URL handler behaviours
     */
    public function testBundleMethodUrlHandler()
    {
        $this->router->handle('/test/method/');

        $this->assertEquals($this->router->getActionNamespace(), 'Test\Action\Method\CliMethodAction');
        $this->assertEquals($this->router->getResponderNamespace(), 'Test\Responder\Method\CliMethodResponder');
        $this->assertEquals($this->router->getAction(), 'CliMethod');
        $this->assertEquals($this->router->getBundle(), 'test');
        //$this->assertEquals($this->router->getParams(), []);
    }

    /**
     * Test bundle and action URL handler behaviours
     */
    public function testBundleActionUrlHandler()
    {
        $this->router->handle('/test/test/');

        $this->assertEquals($this->router->getActionNamespace(), 'Test\Action\TestAction');
        $this->assertEquals($this->router->getResponderNamespace(), 'Test\Responder\TestResponder');
        $this->assertEquals($this->router->getAction(), 'test');
        $this->assertEquals($this->router->getBundle(), 'test');
        //$this->assertEquals($this->router->getParams(), []);
    }

    /**
     * Test bundle and action and params URL handler behaviours
     */
    public function testBundleActionParamsUrlHandler()
    {
        $this->router->handle('/test/test/param1/param2/');

        $this->assertEquals($this->router->getActionNamespace(), 'Test\Action\TestAction');
        $this->assertEquals($this->router->getResponderNamespace(), 'Test\Responder\TestResponder');
        $this->assertEquals($this->router->getAction(), 'test');
        $this->assertEquals($this->router->getBundle(), 'test');
        //$this->assertEquals($this->router->getParams(), ['param1', 'param2']);
    }
}
