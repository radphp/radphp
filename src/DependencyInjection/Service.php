<?php

namespace Rad\DependencyInjection;

use Closure;
use ReflectionClass;
use Rad\DependencyInjection\Service\Exception;

/**
 * Service
 *
 * @package Rad\DependencyInjection
 */
class Service
{
    protected $name;
    protected $definition;
    protected $locked = false;
    protected $shared = false;
    protected $resolved = false;
    protected $resolvedDefinition;

    /**
     * Rad\DependencyInjection\Service constructor
     *
     * @param string                 $name
     * @param callable|object|string $definition
     * @param bool                   $shared
     * @param bool                   $locked
     */
    public function __construct($name, $definition, $shared = false, $locked = false)
    {
        $this->name = $name;
        $this->definition = $definition;
        $this->shared = (bool)$shared;
        $this->locked = (bool)$locked;
    }

    /**
     * Set service name
     *
     * @param string $name
     *
     * @return Service
     */
    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    /**
     * Get service name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set service definition
     *
     * @param callable|object|string $definition
     *
     * @return Service
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get service definition
     *
     * @return callable|object|string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return Service
     */
    public function setLocked($locked)
    {
        $this->locked = (bool)$locked;

        return $this;
    }

    /**
     * Check service is locked
     *
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * Set shared
     *
     * @param boolean $shared
     *
     * @return Service
     */
    public function setShared($shared)
    {
        $this->shared = (bool)$shared;

        return $this;
    }

    /**
     * Check service is shared
     *
     * @return boolean
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * Set resolved
     *
     * @param boolean $resolved
     *
     * @return Service
     */
    public function setResolved($resolved)
    {
        $this->resolved = (bool)$resolved;

        return $this;
    }

    /**
     * Check service is resolved
     *
     * @return boolean
     */
    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * Resolve service
     *
     * @param array $args
     *
     * @return mixed|object
     * @throws Exception
     */
    public function resolve(array $args = null)
    {
        if ($this->resolvedDefinition && $this->shared === true) {
            return $this->resolvedDefinition;
        }

        if ($this->definition instanceof Closure) {
            if ($args) {
                $this->resolvedDefinition = call_user_func_array($this->definition, $args);
            } else {
                $this->resolvedDefinition = call_user_func($this->definition);
            }
        } elseif (is_object($this->definition)) {
            $this->resolvedDefinition = $this->definition;
        } elseif (is_string($this->definition)) {
            if (class_exists($this->definition)) {
                $reflectionObj = new ReflectionClass($this->definition);
                $this->resolvedDefinition = $reflectionObj->newInstanceArgs($args);
            } else {
                throw new Exception(sprintf('Class "%s" does not exist.', $this->definition));
            }
        } else {
            throw new Exception('Class "%s" does not exist.');
        }

        $this->resolved = true;

        return $this->resolvedDefinition;
    }
}
