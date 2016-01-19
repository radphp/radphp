<?php

namespace Rad\Configure\Tests;

use PHPUnit_Framework_TestCase;
use Rad\Configure\Config;

/**
 * Config Test
 *
 * @package Rad\Configure\Tests
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    protected static $fixtures;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        self::$fixtures = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures';
    }

    /**
     * Test load config file
     */
    public function testLoad()
    {
        $this->assertTrue(Config::load(self::$fixtures . '/config.php'));
    }
}
