<?php

namespace Rad\DependencyInjection;

use BadMethodCallException;
use Closure;
use ReflectionClass;

/**
 * Service
 *
 * Represents individually a service in the services container
 *
 * @package Rad\DependencyInjection
 */
class Service implements ServiceInterface
{
    protected $name;
    protected $definition;
    protected $shared;
    protected $sharedInstance;
    protected $resolved = false;

    /**
     * Rad\DependencyInjection\Service
     *
     * @param string  $name
     * @param mixed   $definition
     * @param boolean $shared
     */
    public function __construct($name, $definition, $shared = false)
    {
        $this->name = $name;
        $this->definition = $definition;
        $this->shared = (bool)$shared;
    }

    /**
     * Returns the service's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets if the service is shared or not
     *
     * @param boolean $shared
     */
    public function setShared($shared)
    {
        $this->shared = (bool)$shared;
    }

    /**
     * Check whether the service is shared or not
     *
     * @return boolean
     */
    public function isShared()
    {
        return $this->shared;
    }

    /**
     * Sets/Resets the shared instance related to the service
     *
     * @param mixed $sharedInstance
     */
    public function setSharedInstance($sharedInstance)
    {
        $this->sharedInstance = $sharedInstance;
    }

    /**
     * Set the service definition
     *
     * @param mixed $definition
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;
    }

    /**
     * Returns the service definition
     *
     * @return mixed
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Resolves the service
     *
     * @param array $parameters
     *
     * @return object
     * @throws Exception
     */
    public function resolve(array $parameters = [])
    {
        $instance = null;
        $found = false;

        if ($this->shared && $this->sharedInstance) {
            return $this->sharedInstance;
        }

        if (is_string($this->definition)) {
            $found = true;
            if (class_exists($this->definition)) {
                $class = new ReflectionClass($this->definition);
                if ($parameters) {
                    $instance = $class->newInstanceArgs($parameters);
                    //TODO On version 5.6 use this method:
                    //return new $this->definition(...$parameters);
                } else {
                    $instance = $class->newInstance();
                }
            }
        } elseif (is_object($this->definition)) {
            $found = true;
            if ($this->definition instanceof Closure) {
                if ($parameters) {
                    $instance = call_user_func_array($this->definition, $parameters);
                } else {
                    $instance = call_user_func($this->definition);
                }
            } else {
                $instance = $this->definition;
            }
        } elseif (is_array($this->definition)) {
            //TODO Implement service builder
        }

        if ($found === false) {
            throw new Exception(sprintf('Service "%s" cannot be resolved', $this->name));
        }

        if (!is_object($instance)) {
            throw new Exception('You must save an object in DI');
        }

        if ($this->shared === true) {
            $this->sharedInstance = $instance;
        }

        $this->resolved = true;

        return $instance;
    }

    /**
     * Changes a parameter in the definition without resolve the service
     *
     * @param long  $position
     * @param array $parameter
     *
     * @return Service;
     */
    public function setParameter($position, $parameter)
    {
        //TODO Implement after service builder is created
    }

    /**
     * Returns a parameter in a specific position
     *
     * @param int $position
     *
     * @return array
     */
    public function getParameter($position)
    {
        //TODO Implement after service builder is created
    }

    /**
     * Returns true if the service was resolved
     *
     * @return bool
     */
    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * Restore the internal state of a service
     *
     * @param array $attributes
     *
     * @return Service
     */
    public static function __set_state($attributes)
    {
        if (!isset($attributes['name']) || !isset($attributes['definition']) || !isset($attributes['shared'])) {
            throw new BadMethodCallException('Bad parameters passed to Rad\DependencyInjectionService::__set_state()');
        }

        return self::__construct($attributes['name'], $attributes['definition'], $attributes['shared']);
    }
}
