<?php

namespace Rad\Routing\Tests;

use Composer\Autoload\ClassLoader;
use PHPUnit_Framework_TestCase;
use Rad\Routing\Router;

/**
 * Config Test
 *
 * @package Rad\Configure\Tests
 */
class GenerateUrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Router
     */
    private $router;

    protected function setUp()
    {
        $this->router = new Router();
    }

    /**
     * Test simple URL generator behaviours
     */
    public function testSimpleUrlGenerator()
    {
        $this->assertEquals($this->router->generateUrl(), '/');
        $this->assertEquals($this->router->generateUrl(['url']), '/url');
        $this->assertEquals($this->router->generateUrl(['url', 'test']), '/url/test');
    }
}
