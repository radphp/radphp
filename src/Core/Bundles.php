<?php

namespace Rad\Core;

use Composer\Autoload\ClassLoader;
use Rad\Exception;

/**
 * Bundles Loader
 *
 * @package Rad\Core
 */
class Bundles
{
    /**
     * @var ClassLoader
     */
    protected static $classLoader;
    protected static $bundlesLoaded = [];

    /**
     * Load bundle
     *
     * @param string $bundleName Bundle name
     * @param string $namespace  Bundle namespace
     * @param array  $options    Bundle options
     *
     * @throws Exception
     */
    public static function load($bundleName, $namespace, array $options = [])
    {
        $options += [
            'autoload' => false,
            'baseDir' => 'src',
            'bootstrap' => false
        ];

        $bundlePath = BUNDLES . DS . $bundleName . DS . $options['baseDir'];

        if (is_dir($bundlePath)) {
            self::$bundlesLoaded[$bundleName] = [
                'namespace' => $namespace
            ];

            if ($options['autoload'] === true) {
                if (!self::$classLoader) {
                    self::$classLoader = new ClassLoader();
                }

                self::$classLoader->addPsr4($namespace, $bundlePath);
                self::$classLoader->register();
            }

            if ($options['bootstrap'] === true) {
                $bootstrapClass = $namespace . 'Bootstrap';
                if (class_exists($bootstrapClass)) {
                    new $bootstrapClass();
                } else {
                    throw new Exception(sprintf('Class "%s" does not exist.', $bootstrapClass));
                }
            }
        } else {
            throw new Exception(sprintf('Bundle "%s" does not exist.', $bundleName));
        }
    }

    /**
     * Check bundle is loaded
     *
     * @param string $bundleName Bundle name
     *
     * @return bool
     */
    public static function loaded($bundleName)
    {
        return isset(self::$bundlesLoaded[$bundleName]);
    }

    /**
     * Get bundle namespace
     *
     * @param string $bundleName Bundle name
     *
     * @return string
     * @throws Exception
     */
    public static function getNamespace($bundleName)
    {
        if (isset(self::$bundlesLoaded[$bundleName])) {
            return self::$bundlesLoaded[$bundleName]['namespace'];
        }

        throw new Exception(sprintf('Bundle "%s" does not exist.', $bundleName));
    }
}
