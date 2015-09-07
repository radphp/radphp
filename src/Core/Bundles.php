<?php

namespace Rad\Core;

use Composer\Autoload\ClassLoader;
use Rad\Core\Exception\MissingBundleException;
use Rad\Utility\Inflection;
use RuntimeException;

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
     * @param BundleInterface $bundle
     *
     * @throws MissingBundleException
     */
    public static function load(BundleInterface $bundle)
    {
        if (is_dir($bundle->getPath())) {
            self::$bundlesLoaded[$bundle->getName()] = [
                'namespace' => $bundle->getNamespace(),
                'path' => $bundle->getPath()
            ];

            if (!self::$classLoader) {
                self::$classLoader = new ClassLoader();
            }

            self::$classLoader->addPsr4($bundle->getNamespace(), $bundle->getPath());
            self::$classLoader->register();
        } else {
            throw new MissingBundleException(sprintf('Bundle "%s" could not be found.', $bundle->getName()));
        }
    }

    /**
     * Load all bundles
     *
     * @param array $bundles
     *
     * @throws MissingBundleException
     */
    public static function loadAll(array $bundles)
    {
        foreach ($bundles as $bundle) {
            if (!$bundle instanceof BundleInterface) {
                throw new RuntimeException('Bundle must be instance of "Rad\Core\BundleInterface".');
            }

            self::load($bundle);
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
