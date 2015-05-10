<?php

namespace Rad\Core;

use Composer\Autoload\ClassLoader;
use Rad\Core\Exception\MissingBundleException;
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Exception;
use Rad\Utility\Inflection;

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

        $bundleName = Inflection::camelize($bundleName);
        $bundlePath = BUNDLES . DS . $bundleName . DS . $options['baseDir'];

        if (is_dir($bundlePath)) {
            self::$bundlesLoaded[$bundleName] = [
                'namespace' => $namespace,
                'path' => $bundlePath
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
                    $bootstrapInstance = new $bootstrapClass();
                    if ($bootstrapInstance instanceof ContainerAwareInterface) {
                        $bootstrapInstance->setContainer(new Container());
                    }
                } else {
                    throw new Exception(sprintf('Class "%s" does not exist.', $bootstrapClass));
                }
            }
        } else {
            throw new MissingBundleException(sprintf('Bundle "%s" could not be found.', $bundleName));
        }
    }

    /**
     * Check bundle is loaded
     *
     * @param string $bundleName Bundle name
     *
     * @return bool
     */
    public static function isLoaded($bundleName)
    {
        $bundleName = Inflection::camelize($bundleName);

        return isset(self::$bundlesLoaded[$bundleName]);
    }

    /**
     * Get all loaded bundles
     *
     * @return array
     */
    public static function getLoaded()
    {
        return array_keys(self::$bundlesLoaded);
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
        $bundleName = Inflection::camelize($bundleName);

        if (isset(self::$bundlesLoaded[$bundleName])) {
            return self::$bundlesLoaded[$bundleName]['namespace'];
        }

        throw new MissingBundleException(sprintf('Bundle "%s" could not be found.', $bundleName));
    }

    /**
     * Get bundle path
     *
     * @param string $bundleName Bundle name
     *
     * @return string
     * @throws Exception
     */
    public static function getPath($bundleName)
    {
        $bundleName = Inflection::camelize($bundleName);

        if (isset(self::$bundlesLoaded[$bundleName])) {
            return self::$bundlesLoaded[$bundleName]['path'];
        }

        throw new MissingBundleException(sprintf('Bundle "%s" could not be found.', $bundleName));
    }
}