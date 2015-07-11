<?php

namespace Rad\Core;

use Composer\Autoload\ClassLoader;
use Rad\Core\Exception\BaseException;
use Rad\Core\Exception\MissingBundleException;
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
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
     * @throws BaseException
     * @throws MissingBundleException
     */
    public static function load($bundleName, array $options = [])
    {
        $options += [
            'autoload' => true
        ];

        $bundlePath = SRC_DIR . DS . $bundleName;

        if (is_dir($bundlePath)) {
            $namespace = $bundleName . '\\';
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
     * @throws MissingBundleException
     */
    public static function getNamespace($bundleName)
    {
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
     * @throws MissingBundleException
     */
    public static function getPath($bundleName)
    {
        if (isset(self::$bundlesLoaded[$bundleName])) {
            return self::$bundlesLoaded[$bundleName]['path'];
        }

        throw new MissingBundleException(sprintf('Bundle "%s" could not be found.', $bundleName));
    }
}
