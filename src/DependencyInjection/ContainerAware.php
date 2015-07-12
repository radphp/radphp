<?php

namespace Rad\DependencyInjection;

use BadMethodCallException;
use Rad\Utility\Inflection;

/**
 * ContainerAware
 *
 * @package Rad\DependencyInjection
 */
abstract class ContainerAware implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Set container
     *
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get container
     *
     * @return Container
     */
    public function getContainer()
    {
        if (!is_object($this->container)) {
            return $this->container = Container::getInstance();
        }

        return $this->container;
    }

    /**
     * Magic call
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed|object
     */
    public function __call($method, $args)
    {
        if (stripos($method, 'get', 0) !== false) {
            return $this->getContainer()->get(Inflection::underscore(substr($method, 3)), $args);
        } else {
            throw new BadMethodCallException(sprintf('Method "%s" does not exist.', $method));
        }
    }

    /**
     * Magic get
     *
     * @param string $property Service name
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->get($property);
    }

    /**
     * Get service
     *
     * @param string $serviceName Service name
     *
     * @return mixed|null|object
     * @throws Exception
     */
    public function get($serviceName)
    {
        if ($this->getContainer()->has($serviceName)) {
            return $this->getContainer()->get($serviceName);
        }

        return null;
    }
}
