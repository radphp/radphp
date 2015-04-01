<?php

namespace Rad\DependencyInjection;

/**
 * Class Di
 *
 * Rad\DependencyInjection\Di is a component that implements Dependency Injection/Service Location
 * of services and it’s itself a container for them.
 *
 * @package Rad\DependencyInjection
 */
class Di implements DiInterface
{
    /**
     * @var ServiceInterface[]
     */
    protected $services = [];
    protected $shared = [];
    protected static $default;
    protected $fresh = false;

    /**
     * Rad\DI\Di constructor
     */
    public function __construct()
    {
        if (is_null(self::$default)) {
            self::$default = $this;
        }
    }

    /**
     * Magic method to get or set services using setters/getters
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $method = substr($name, 0, 3);
        $service = strtolower(substr($name, 3));

        if ($method && ($method == 'set' || $method == 'get')) {
            if ($method == 'set') {
                return $this->set($service, $arguments[0]);
            } else {
                if (isset($this->services[$service])) {
                    return $this->get($service, $arguments);
                } else {
                    throw new Exception(
                        sprintf('Service "%s" was not found in the dependency injection container', $service)
                    );
                }
            }
        } else {
            throw new Exception(sprintf('Call to undefined method "%s"', $name));
        }
    }

    /**
     * Registers a service in the services container
     *
     * @param string  $name
     * @param mixed   $definition
     * @param boolean $shared
     *
     * @return ServiceInterface
     */
    public function set($name, $definition, $shared = false)
    {
        $this->services[$name] = new Service($name, $definition, $shared);

        return $this->services[$name];
    }

    /**
     * Registers an "always shared" service in the services container
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return ServiceInterface
     */
    public function setShared($name, $definition)
    {
        return $this->set($name, $definition, true);
    }

    /**
     * Removes a service in the services container
     *
     * @param string $name
     */
    public function remove($name)
    {
        if (isset($this->services[$name])) {
            unset($this->services[$name]);
        }
    }

    /**
     * Attempts to register a service in the services container
     * Only is successful if a service hasn't been registered previously
     * with the same name
     *
     * @param string  $name
     * @param mixed   $definition
     * @param boolean $shared
     *
     * @return ServiceInterface
     */
    public function attempt($name, $definition, $shared = false)
    {
        if (!isset($this->services[$name])) {
            return $this->set($name, $definition, $shared);
        } else {
            return $this->services[$name];
        }
    }

    /**
     * Returns a service definition without resolving
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function getRaw($name)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name]->getDefinition();
        }

        throw new Exception(sprintf('Service "%s" was not found in the dependency injection container', $name));
    }

    /**
     * Returns a Rad\DI\Service instance
     *
     * @param string $name
     *
     * @return ServiceInterface
     * @throws Exception
     */
    public function getService($name)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        throw new Exception(sprintf('Service "%s" was not found in the dependency injection container', $name));
    }

    /**
     * Resolves the service based on its configuration
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     * @throws Exception
     */
    public function get($name, $parameters = [])
    {
        if (isset($this->services[$name])) {
            $output = $this->services[$name]->resolve($parameters);

            if ($output && $output instanceof InjectionAwareInterface) {
                $output->setDI($this);
            }

            return $output;
        }

        throw new Exception(sprintf('Service "%s" was not found in the dependency injection container', $name));
    }

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
    public function getShared($name, $parameters = [])
    {
        if (isset($this->shared[$name])) {
            $this->fresh = false;
            return $this->shared[$name];
        }

        if (isset($this->services[$name])) {
            $output = $this->services[$name]->resolve($parameters);

            if ($output && $output instanceof InjectionAwareInterface) {
                $output->setDI($this);
            }

            $this->shared[$name] = $output;
            $this->fresh = true;
            return $output;
        } else {
            throw new Exception(sprintf('Service "%s" was not found in the dependency injection container', $name));
        }
    }

    /**
     * Check whether the DI contains a service by a name
     *
     * @param string $name Service name
     *
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->services);
    }

    /**
     * Check whether the last service obtained via getShared produced a fresh instance or an existing one
     *
     * @return bool
     */
    public function wasFreshInstance()
    {
        return $this->fresh;
    }

    /**
     * Return the services registered in the DI
     *
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Set a default dependency injection container to be obtained into static methods
     *
     * @param DiInterface $dependencyInjector
     */
    public static function setDefault(DiInterface $dependencyInjector)
    {
        self::$default = $dependencyInjector;
    }

    /**
     * Return the latest DI created
     *
     * @return DiInterface
     */
    public static function getDefault()
    {
        return self::$default;
    }

    /**
     * Resets the internal default DI
     */
    public static function reset()
    {
        self::$default = null;
    }

    /**
     * Check if a service is registered using the array syntax.
     * Alias for DI::has()
     *
     * @param string $name
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Allows to obtain a shared service using the array syntax.
     * Alias for DI::getShared()
     *
     * @param string $name
     *
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->getShared($name);
    }

    /**
     * Allows to register a shared service using the array syntax.
     * Alias for DI::setShared()
     *
     * @param string $name Service name
     * @param mixed  $definition
     */
    public function offsetSet($name, $definition)
    {
        $this->setShared($name, $definition);
    }

    /**
     * Removes a service from the services container using the array syntax.
     * Alias for DI::remove()
     *
     * @param string $name Service name
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }
}
