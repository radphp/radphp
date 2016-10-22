<?php

namespace Rad\Configure;

use ArrayAccess;
use Serializable;

/**
 * RadPHP Config
 *
 * Config is designed to simplify the access to, and the use of, configuration data within applications.
 *
 * @package Rad\Configure
 */
class Config implements ArrayAccess, Serializable
{
    use ConfigurableTrait {
        load as private internalLoad;
    }

    /**
     * Load config
     *
     * @param LoaderInterface $loader Config loader
     * @param bool            $merge  Is config merged or overwrite
     */
    public function load(LoaderInterface $loader, $merge = true)
    {
        $this->internalLoad($loader->load(), $merge);
    }

    /**
     * Dump config
     *
     * @param DumperInterface $dumper Config dumper
     *
     * @return bool
     */
    public function dump(DumperInterface $dumper)
    {
        return $dumper->dump($this->configsContainer);
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
     * @throws Exception
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        throw new Exception('Can not unset value');
    }

    /**
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->configsContainer);
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
        $this->configsContainer = unserialize($serialized);
    }
}
