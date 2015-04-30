<?php

namespace Rad\DependencyInjection\Service;

use ArrayAccess;
use Rad\Core\SingletonTrait;
use Rad\DependencyInjection\Service;

/**
 * Service Locator
 *
 * @package Rad\DependencyInjection\Service
 */
class Locator implements ArrayAccess
{
    use SingletonTrait;

    /**
     * @var Service[]
     */
    protected static $services = [];

    /**
     * Set service
     *
     * @param string                 $name
     * @param callable|object|string $definition
     * @param bool                   $shared
     * @param bool                   $locked
     *
     * @throws Exception
     */
    public static function set($name, $definition, $shared = false, $locked = false)
    {
        if (isset(self::$services[$name]) && self::$services[$name]->isLocked()) {
            throw new Exception(sprintf('Service "%s" does exist and locked.', $name));
        }

        self::$services[$name] = new Service($name, $definition, $shared, $locked);
    }

    /**
     * Set service as shared
     *
     * @param string                 $name
     * @param callable|object|string $definition
     * @param bool                   $locked
     *
     * @throws Exception
     */
    public static function setShared($name, $definition, $locked = false)
    {
        if (isset(self::$services[$name]) && self::$services[$name]->isLocked()) {
            throw new Exception(sprintf('Service "%s" does exist and locked.', $name));
        }

        self::$services[$name] = new Service($name, $definition, true, $locked);
    }

    /**
     * Get and resolve service
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed|object
     * @throws Exception
     */
    public static function get($name, array $args = null)
    {
        if (!isset(self::$services[$name])) {
            throw new Exception(sprintf('Service "%s" does not exist.', $name));
        }

        return self::$services[$name]->resolve($args);
    }

    /**
     * Check service is exist
     *
     * @param string $name
     *
     * @return bool
     */
    public static function has($name)
    {
        return isset(self::$services[$name]);
    }

    /**
     * Remove service
     *
     * @param string $name
     *
     * @throws Exception
     */
    public static function remove($name)
    {
        if (isset(self::$services[$name]) && self::$services[$name]->isLocked()) {
            throw new Exception(sprintf('You can not remove locked service.', $name));
        }

        unset(self::$services[$name]);
    }

    /**
     * Whether a offset exists
     *
     * @param string $offset An offset to check for.
     *
     * @link            http://php.net/manual/en/arrayaccess.offsetexists.php
     * @return boolean true on success or false on failure.
     *                  The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
