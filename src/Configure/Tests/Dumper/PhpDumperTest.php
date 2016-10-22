<?php

namespace Rad\Configure\Tests\Dumper;

use PHPUnit_Framework_TestCase;
use Rad\Configure\Config;
use Rad\Configure\Dumper\PhpDumper;
use Rad\Configure\Exception;
use Rad\Configure\Loader\PhpLoader;

/**
 * PhpDumper Test
 *
 * @package Rad\Configure\Tests\Dumper
 */
class PhpDumperTest extends PHPUnit_Framework_TestCase
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
     * testDump
     */
    public function testDump()
    {
        $config = new Config();
        $filename = sprintf(sys_get_temp_dir() . '/%s/config.php', uniqid());

        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));
        $this->assertTrue($config->dump(new PhpDumper($filename)));

        $config->load(new PhpLoader($filename), false);
        $this->assertEquals('val2', $config->get('key2.sub-key1.sub-sub-key2'));
    }

    /**
     * testDumpDestinationNotWritableThrowingException
     *
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /^File "(.*)" is not writable/
     */
    public function testDumpDestinationNotWritableThrowingException()
    {
        $config = new Config();
        $tmpFile = tempnam(sys_get_temp_dir(), 'TestConfigure');
        chmod($tmpFile, 0400);

        $config->load(new PhpLoader(self::$fixtures . '/base_config.php'));
        $config->dump(new PhpDumper($tmpFile));
    }
}
