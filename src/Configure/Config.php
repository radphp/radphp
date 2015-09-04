<?php

namespace Rad\Configure;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use Rad\Configure\Engine\PhpConfig;
use Rad\Core\SingletonTrait;
use Serializable;

/**
 * RadPHP Config
 *
 * Config is designed to simplify the access to, and the use of, configuration data within applications.
 *
 * @package Rad\Configure
 */
class Config implements ArrayAccess, Iterator, Serializable, JsonSerializable, Countable
{
    use SingletonTrait;

    /**
     * Store config
     *
     * @var array
     */
    protected static $container = [];

    /**
     * Store registered engine
     *
     * @var EngineInterface[]
     */
    protected static $engines = [];

    /**
     * Load config
     *
     * @param mixed  $config     Config data for passed to engine load method
     * @param string $engineName Engine registered name
     * @param bool   $merge      Is config merged or overwrite
     *
     * @return bool
     */
    public static function load($config, $engineName = 'default', $merge = true)
    {
        if ($engine = self::getEngine($engineName)) {
            if ($merge) {
                self::mergeConfig($engine->load($config), self::$container);
            } else {
                self::$container = $engine->load($config);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Dump config
     *
     * @param  string $file       File path for save config
     * @param string  $engineName Engine name for use in dump
     *
     * @return bool
     */
    public static function dump($file, $engineName = 'default')
    {
        if ($engine = self::getEngine($engineName)) {
            return $engine->dump($file, self::$container);
        }

        return false;
    }

    /**
     * Set config
     *
     * @param string $identifier Parameter name.
     * @param mixed  $value      Value to set
     */
    public static function set($identifier, $value)
    {
        $ids = explode('.', $identifier);
        $base = &self::$container;

        while ($current = array_shift($ids)) {
            if (is_array($base) && array_key_exists($current, $base)) {
                $base = &$base[$current];
            } else {
                $base[$current] = [];
                $base = &$base[$current];
            }
        }

        $base = $value;
    }

    /**
     * Get config
     *
     * @param string $identifier Parameter name.
     * @param null   $default    Default value
     *
     * @return array|null
     */
    public static function get($identifier, $default = null)
    {
        $value = self::getInternal($identifier);

        if (is_null($value)) {
            return $default;
        }

        return $value;
    }

    /**
     * Indicates whether parameter exists or not
     *
     * @param string $identifier Parameter name.
     *
     * @return bool
     */
    public static function has($identifier)
    {
        return self::getInternal($identifier) !== null;
    }

    /**
     * Register engine
     *
     * @param string          $name   Engine name
     * @param EngineInterface $engine Engine instance
     */
    public static function registerEngine($name, EngineInterface $engine)
    {
        self::$engines[$name] = $engine;
    }

    /**
     * Get engine
     *
     * @param string $name Engine name
     *
     * @return bool|EngineInterface
     */
    protected static function getEngine($name)
    {
        if (!isset(self::$engines[$name])) {
            if ($name !== 'default') {
                return false;
            }
            self::registerEngine($name, new PhpConfig());
        }

        return self::$engines[$name];
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @return boolean true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
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
        return $this->getInternal($offset);
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
        self::set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     *
     * @throws Exception
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        throw new Exception('Can not unset value');
    }

    /**
     * Retrieves parameter
     *
     * @param string $identifier Parameter name.
     *
     * @return array|null
     */
    protected static function getInternal($identifier)
    {
        $ids = explode('.', $identifier);
        $base = &self::$container;

        while ($current = array_shift($ids)) {
            if (is_array($base) && array_key_exists($current, $base)) {
                $base = &$base[$current];
            } else {
                return null;
            }
        }

        $result = $base;

        return $result;
    }

    /**
     * Merge config
     *
     * @param mixed $newData
     * @param array $baseConfig
     */
    protected static function mergeConfig($newData, &$baseConfig)
    {
        if (is_array($newData)) {
            foreach ($newData as $key => $value) {
                if (isset($baseConfig[$key])) {
                    self::mergeConfig($value, $baseConfig[$key]);
                } else {
                    $baseConfig[$key] = $value;
                }
            }
        } else {
            $baseConfig = $newData;
        }
    }

    /**
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count(self::$container);
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current(self::$container);
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next(self::$container);
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key(self::$container);
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset(self::$container);
    }

    /**
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(self::$container);
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
        self::$container = unserialize($serialized);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by "json_encode",
     *       which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return self::$container;
    }
}
