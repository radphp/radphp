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
    }

    protected function setUp()
    {
        $this->router = new Router();
    }

    /**
     * Test simple URL generator behaviours
     */
    public function testOtherStuff()
    {
        $this->assertEquals($this->router->getPrefix($this->router->setPrefix(['hello', 'world'])), ['hello', 'world']);
    }
}
