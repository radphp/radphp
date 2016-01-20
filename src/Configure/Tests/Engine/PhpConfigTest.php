<?php

namespace Rad\Configure\Tests\Engine;

use PHPUnit_Framework_TestCase;
use Rad\Configure\Config;

/**
 * PhpConfig Engine Test
 *
 * @package Rad\Configure\Tests\Engine
 */
class PhpConfigTest extends PHPUnit_Framework_TestCase
{
    protected static $fixtures;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        self::$fixtures = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures';
    }

    /**
     * Test load php config file
     */
    public function testLoad()
    {
        $this->assertTrue(Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php'));

        $this->setExpectedExceptionRegExp(
            'Rad\Configure\Exception',
            '/Input file "not-exist-php-config-file.php" is not exist or is not readable/'
        );
        Config::load('not-exist-php-config-file.php');
    }

    /**
     * Test load invalid return config file
     */
    public function testLoadInvalidReturnConfig()
    {
        $this->setExpectedExceptionRegExp(
            'Rad\Configure\Exception',
            '/You must return array in config file/'
        );
        Config::load(self::$fixtures . '/Engine/PhpConfig/invalid_return_config.php');
    }

    /**
     * Test load from array
     */
    public function testLoadFromArray()
    {
        $this->assertTrue(Config::load(['alice' => 'bob']));
        $this->assertEquals(Config::get('alice'), 'bob');

        $this->setExpectedExceptionRegExp(
            'Rad\Configure\Exception',
            '/Input data is not valid/'
        );
        Config::load(new \stdClass('Input data is not valid'));
    }

    /**
     * Test PhpConfig dump
     */
    public function testDump()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        $filename = sprintf('/tmp/%s/config.php', uniqid());

        $this->assertTrue(Config::dump($filename));
        Config::load($filename, 'default', false);
        $this->assertEquals(Config::get('key2.sub-key1.sub-sub-key2'), 'val2');

        chmod($filename, 0400);
        $this->setExpectedExceptionRegExp(
            'Rad\Configure\Exception',
            sprintf('/File "%s" is not writable/', preg_quote($filename, '/'))
        );
        Config::dump($filename);
    }
}
