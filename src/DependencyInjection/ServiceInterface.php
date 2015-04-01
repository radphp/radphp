<?php

namespace Rad\DependencyInjection;

/**
 * Service Interface
 *
 * @package Rad\DependencyInjection
 */
interface ServiceInterface
{
    /**
     * Returns the service's name
     *
     * @return string
     */
    public function getName();

    /**
     * Sets if the service is shared or not
     *
     * @param boolean $shared
     */
    public function setShared($shared);

    /**
     * Check whether the service is shared or not
     *
     * @return boolean
     */
    public function isShared();

    /**
     * Set the service definition
     *
     * @param mixed $definition
     */
    public function setDefinition($definition);

    /**
     * Returns the service definition
     *
     * @return mixed
     */
    public function getDefinition();

    /**
     * Resolves the service
     *
     * @param array $parameters
     *
     * @return object
     */
    public function resolve(array $parameters = []);

    /**
     * Returns true if the service was resolved
     *
     * @return bool
     */
    public function isResolved();
}
