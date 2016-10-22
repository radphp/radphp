<?php

namespace Rad\Configure\Tests;

use PHPUnit_Framework_TestCase;
use Rad\Configure\Config;
use Rad\Configure\Dumper\PhpDumper;
use Rad\Configure\Exception;
use Rad\Configure\Loader\PhpLoader;

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
     * testSet
     */
    public function testSet()
    {
        $config = new Config();

        $config->set('key.exists', 'ok');
        $this->assertEquals('ok', $config->get('key.exists'));

        $config['debug'] = true;
        $this->assertTrue($config->get('debug'));

        $expected = [
            'key1' => [
                'sub-key1' => [
                    'sub-sub-key1' => [
                        'sub-sub-sub-key1' => [
                            'sub-sub-sub-sub-key1' => 'Hi!!!'
                        ]
                    ]
                ]
            ]
        ];
        $config->set('key', $expected);
        $result = $config->get('key');
        $this->assertEquals($expected, $result);
        $this->assertEquals($expected['key1'], $config->get('key.key1'));
        $this->assertEquals($expected['key1']['sub-key1'], $config->get('key.key1.sub-key1'));
        $this->assertEquals(
            'Hi!!!',
            $config->get('key.key1.sub-key1.sub-sub-key1.sub-sub-sub-key1.sub-sub-sub-sub-key1')
        );
    }

    /**
     * testGet
     */
    public function testGet()
    {
        $config = new Config();

        $config->set('key1.sub-key1.sub-sub-key1', 'ok');
        $config->set('key1.sub-key1.sub-sub-key2', 'something_else');
        $this->assertEquals($config->get('key1.sub-key1.sub-sub-key1'), 'ok');
        $this->assertEquals('something_else', $config->get('key1.sub-key1.sub-sub-key2'));

        $this->assertTrue(is_array($config['key1']));
        $this->assertTrue(isset($config['key1']));

        $this->assertNull($config->get('key2'), 'Missing key should return null.');
    }

    /**
     * testLoad
     */
    public function testLoad()
    {
        $config = new Config();
        $config->load(new PhpLoader(['debug' => true]));

        $this->assertEquals($config->get('debug'), true, 'Should load PHP array.');
        $this->assertNotEquals($config->get('debug'), 'true', 'Should not change value type.');
    }

    /**
     * testLoadWithMerge
     */
    public function testLoadWithMerge()
    {
        $config = new Config();

        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));
        $this->assertEquals('val2', $config->get('key2.sub-key1.sub-sub-key2'), 'Should load PHP config file.');

        $config->load(new PhpLoader(self::$fixtures . '/other_config.php'));
        $this->assertEquals(
            $config->get('key2.sub-key1.sub-sub-key2'),
            'changed-val2',
            'Should merge new config with old config.'
        );
    }

    /**
     * testLoadWithoutMerge
     */
    public function testLoadWithoutMerge()
    {
        $config = new Config();

        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));
        $this->assertEquals('bar', $config->get('foo'));

        $config->load(new PhpLoader(self::$fixtures . '/other_config.php'), false);
        $this->assertNull($config->get('foo'));
    }

    /**
     * testDump
     */
    public function testDump()
    {
        $config = new Config();
        $tmpFile = tempnam(sys_get_temp_dir(), 'TestConfigure');

        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));
        $this->assertTrue($config->dump(new PhpDumper($tmpFile)));

        $config->load(new PhpLoader($tmpFile), false);
        $this->assertEquals('val2', $config->get('key2.sub-key1.sub-sub-key2'));
    }

    /**
     * testHas
     */
    public function testHas()
    {
        $config = new Config();
        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));
        $this->assertTrue($config->has('foo'));
        $this->assertFalse($config->has('not-exists-key'));
    }

    /**
     * testHasSavedEmpty
     */
    public function testHasSavedEmpty()
    {
        $config = new Config();

        $config->set('key', 0);
        $this->assertTrue($config->has('key'));

        $config->set('key', '0');
        $this->assertTrue($config->has('key'));

        $config->set('key', false);
        $this->assertTrue($config->has('key'));

        $config->set('key', null);
        $this->assertFalse($config->has('key'));
    }

    /**
     * testHasKeyWithSpaces
     */
    public function testHasKeyWithSpaces()
    {
        $config = new Config();

        $config->set('config key', 'value');
        $this->assertTrue($config->has('config key'));

        $config->set('config key.test KEY', 'test value');
        $this->assertTrue($config->has('config key.test KEY'));
    }

    /**
     * testUnsetIdentifierThrowingException
     *
     * @expectedException Exception
     * @expectedExceptionMessage Can not unset value
     */
    public function testUnsetIdentifierThrowingException()
    {
        $config = new Config();

        $config->set('foo', 'bar');
        unset($config['foo']);
    }

    /**
     * testSerializableInterface
     */
    public function testSerializableInterface()
    {
        $config = new Config();
        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));
        $config = unserialize(serialize($config));

        $this->assertEquals($config['foo'], 'bar');
    }
}
