<?php

namespace Rad\Configure\Tests\Loader;

use PHPUnit_Framework_TestCase;
use Rad\Configure\Config;
use Rad\Configure\Exception;
use Rad\Configure\Loader\PhpLoader;

/**
 * PhpLoader Test
 *
 * @package Rad\Configure\Tests\Loader
 */
class PhpLoaderTest extends PHPUnit_Framework_TestCase
{
    protected static $fixtures;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        self::$fixtures = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixtures';
    }

    /**
     * testLoadPhpFile
     */
    public function testLoadPhpFile()
    {
        $config = new Config();
        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));

        $this->assertEquals('bar', $config->get('foo'));
    }

    /**
     * testLoadWithPhpArray
     */
    public function testLoadWithPhpArray()
    {
        $config = new Config();
        $config->load(new PhpLoader(['debug' => true]));

        $this->assertTrue($config->get('debug'));
    }

    /**
     * testLoadPhpFileNotExistThrowingException
     *
     * @expectedException Exception
     * @expectedExceptionMessage Input file "file_does_not_exist.php" is not exist or is not readable
     */
    public function testLoadPhpFileNotExistThrowingException()
    {
        $config = new Config();
        $config->load(new PhpLoader('file_does_not_exist.php'));
    }

    /**
     * testLoadPhpFileInvalidReturnThrowingException
     *
     * @expectedException Exception
     * @expectedExceptionMessage You must return array in config file
     */
    public function testLoadPhpFileInvalidReturnThrowingException()
    {
        $config = new Config();
        $config->load(new PhpLoader(self::$fixtures . '/invalid_return_config.php'));
    }
}
