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
        $this->assertTrue(Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php'));
        $this->assertEquals(Config::get('foo'), 'bar');

        Config::load(self::$fixtures . '/Engine/PhpConfig/other_config.php');
        $this->assertEquals(Config::get('key2.sub-key1.sub-sub-key2'), 'changed-val2');

        Config::load(self::$fixtures . '/Engine/PhpConfig/other_config.php', 'default', false);
        $this->assertNotEquals(Config::get('foo'), 'bar');

        $this->assertFalse(Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php', 'not_exists_engine'));
    }

    /**
     * Test dump config file
     */
    public function testDump()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        $tmpFile = tempnam(sys_get_temp_dir(), 'TestConfigure');

        $this->assertFalse(Config::dump($tmpFile, 'not_exists_engine'));
        $this->assertTrue(Config::dump($tmpFile));

        Config::load($tmpFile, 'default', false);
        $this->assertEquals(Config::get('key2.sub-key1.sub-sub-key2'), 'val2');
    }

    /**
     * Test set config
     */
    public function testSet()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        Config::set('foo', 'new-bar');
        $this->assertEquals(Config::get('foo'), 'new-bar');

        Config::set('key1.sub-key1', 'new-val1');
        $this->assertEquals(Config::get('key1.sub-key1'), 'new-val1');

        Config::set('key1.sub-new-key1', 'val1');
        $this->assertEquals(Config::get('key1.sub-new-key1'), 'val1');
    }

    /**
     * Test get config
     */
    public function testGet()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        $this->assertEquals(Config::get('foo'), 'bar');
        $this->assertEquals(Config::get('key0', 'defaultValue'), 'defaultValue');
    }

    /**
     * Test has exist identifier
     */
    public function testHas()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        $this->assertTrue(Config::has('foo'));
        $this->assertFalse(Config::has('not-exists-key'));
    }

    /**
     * Test array access interface
     */
    public function testArrayAccessInterface()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        $configObject = Config::getInstance();

        $this->assertTrue(isset($configObject['foo']));
        $this->assertEquals($configObject['foo'], 'bar');

        $configObject['foo'] = 'new-bar';
        $this->assertEquals($configObject['foo'], 'new-bar');

        $this->setExpectedExceptionRegExp('Rad\Configure\Exception', '/Can not unset value/');
        unset($configObject['foo']);
    }

    /**
     * Test serializable interface
     */
    public function testSerializableInterface()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        $configObject = unserialize(serialize(Config::getInstance()));

        $this->assertEquals($configObject['foo'], 'bar');
    }

    /**
     * Test json serialize interface
     */
    public function testJsonSerializeInterface()
    {
        Config::load(self::$fixtures . '/Engine/PhpConfig/base_config.php');
        $configArray = json_decode(json_encode(Config::getInstance()), true);

        $this->assertEquals($configArray['foo'], 'bar');
    }
}
