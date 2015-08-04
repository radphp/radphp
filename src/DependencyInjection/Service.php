<?php

namespace Rad\DependencyInjection;

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
     * @param Container $container
     * @param array     $args
     *
     * @return mixed|object
     * @throws Exception
     */
    public function resolve(Container $container, array $args = [])
    {
        if ($this->resolvedDefinition && $this->shared === true) {
            return $this->resolvedDefinition;
        }

        $definitionResolver = new DefinitionResolver($container);
        $this->resolvedDefinition = $definitionResolver->resolver($this->definition, $args);
        $this->resolved = true;

        return $this->resolvedDefinition;
    }
}
