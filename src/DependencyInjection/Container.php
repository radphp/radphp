<?php

namespace Rad\DependencyInjection;

use Rad\DependencyInjection\Service\Locator;

/**
 * Container
 *
 * @package Rad\DependencyInjection
 */
class Container
{
    /**
     * @var Locator
     */
    protected $serviceLocator;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Rad\DependencyInjection\Container constructor
     */
    public function __construct()
    {
        $this->serviceLocator = Locator::getInstance();
        $this->registry = Registry::getInstance();
    }

    /**
     * Set service
     *
     * @param string          $name
     * @param callable|string $definition
     * @param bool            $shared
     * @param bool            $locked
     *
     * @return Container
     */
    public function setService($name, $definition, $shared = false, $locked = false)
    {
        $this->serviceLocator->set($name, $definition, $shared, $locked);

        return $this;
    }

    /**
     * Set shared service
     *
     * @param string          $name
     * @param callable|string $definition
     * @param bool            $locked
     *
     * @return Container
     * @throws Service\Exception
     */
    public function setSharedService($name, $definition, $locked = false)
    {
        $this->serviceLocator->setShared($name, $definition, $locked);

        return $this;
    }

    /**
     * Get service
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed|object
     */
    public function getService($name, array $args = null)
    {
        return $this->serviceLocator->get($name, $args);
    }

    /**
     * Check service is exist
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasService($name)
    {
        return $this->serviceLocator->has($name);
    }

    /**
     * Remove service
     *
     * @param string $name
     */
    public function removeService($name)
    {
        $this->serviceLocator->remove($name);
    }

    /**
     * Store in registry
     *
     * @param string $key
     * @param mixed  $value
     * @param string $scope
     *
     * @return Container
     */
    public function set($key, $value, $scope = Registry::DEFAULT_SCOPE)
    {
        $this->registry->set($key, $value, $scope);

        return $this;
    }

    /**
     * Get key from registry
     *
     * @param string $key
     * @param string $scope
     *
     * @return mixed
     */
    public function get($key, $scope = Registry::DEFAULT_SCOPE)
    {
        return $this->registry->get($key, $scope);
    }

    /**
     * Check exist key
     *
     * @param string $key
     * @param string $scope
     *
     * @return bool
     */
    public function has($key, $scope = Registry::DEFAULT_SCOPE)
    {
        return $this->registry->has($key, $scope);
    }

    /**
     * Remove key from registry
     *
     * @param string $key
     * @param string $scope
     */
    public function remove($key, $scope = Registry::DEFAULT_SCOPE)
    {
        $this->registry->remove($key, $scope);
    }
}
