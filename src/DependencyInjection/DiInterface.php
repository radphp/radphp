<?php

namespace Rad\DependencyInjection;

/**
 * Di Interface
 *
 * @package Rad\DependencyInjection
 */
interface DiInterface extends \ArrayAccess
{
    /**
     * Registers a service in the services container
     *
     * @param string  $name
     * @param mixed   $definition
     * @param boolean $shared
     *
     * @return ServiceInterface
     */
    public function set($name, $definition, $shared = false);

    /**
     * Registers an "always shared" service in the services container
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return ServiceInterface
     */
    public function setShared($name, $definition);

    /**
     * Removes a service in the services container
     *
     * @param string $name
     */
    public function remove($name);

    /**
     * Returns a Rad\DI\Service instance
     *
     * @param string $name
     *
     * @return ServiceInterface
     * @throws Exception
     */
    public function getService($name);

    /**
     * Resolves the service based on its configuration
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     * @throws Exception
     */
    public function get($name, $parameters = []);

    /**
     * Resolves a service, the resolved service is stored in the DI, subsequent
     * requests for this service will return the same instance
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     * @throws Exception
     */
    public function getShared($name, $parameters = []);

    /**
     * Check whether the DI contains a service by a name
     *
     * @param string $name Service name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Check whether the last service obtained via getShared produced a fresh instance or an existing one
     *
     * @return bool
     */
    public function wasFreshInstance();

    /**
     * Return the services registered in the DI
     *
     * @return Service[]
     */
    public function getServices();
}
