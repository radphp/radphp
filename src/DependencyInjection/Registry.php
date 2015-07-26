<?php

namespace Rad\DependencyInjection;

use ArrayAccess;
use Serializable;
use JsonSerializable;
use Rad\Core\SingletonTrait;

/**
 * Registry
 *
 * @package Rad\DependencyInjection
 */
class Registry implements ArrayAccess, Serializable, JsonSerializable
{
    use SingletonTrait;

    protected static $storage = [self::DEFAULT_SCOPE => []];
    const DEFAULT_SCOPE = 'default';

    /**
     * Store key in registry
     *
     * @param string $key
     * @param mixed  $value
     * @param string $scope
     */
    public static function set($key, $value, $scope = self::DEFAULT_SCOPE)
    {
        self::$storage[$scope][$key] = $value;
    }

    /**
     * Get key from registry
     *
     * @param string $key
     * @param string $scope
     *
     * @return null
     */
    public static function get($key, $scope = self::DEFAULT_SCOPE)
    {
        if (isset(self::$storage[$scope][$key])) {
            return self::$storage[$scope][$key];
        }

        return null;
    }

    /**
     * Check key exist or not
     *
     * @param string $key
     * @param string $scope
     *
     * @return bool
     */
    public static function has($key, $scope = self::DEFAULT_SCOPE)
    {
        return isset(self::$storage[$scope][$key]);
    }

    /**
     * @param        $key
     * @param string $scope
     */
    public static function remove($key, $scope = self::DEFAULT_SCOPE)
    {
        unset(self::$storage[$scope][$key]);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
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

    /**
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(self::$storage);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized The string representation of the object.
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @return void
     */
    public function unserialize($serialized)
    {
        self::$storage = unserialize($serialized);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by json_encode,
     *                which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return self::$storage;
    }
}
