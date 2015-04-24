<?php

namespace Rad\DependencyInjection;

use Rad\Event\EventDispatcher;

/**
 * Injectable
 *
 * This class allows to access services in the services container by just only
 * accessing a public property with the same name of a registered service
 *
 * @property EventDispatcher $event
 *
 * @package Rad\DependencyInjection
 */
abstract class Injectable implements InjectionAwareInterface
{
    protected $dependencyInjector;

    /**
     * Sets the dependency injector
     *
     * @param DiInterface $dependencyInjector
     */
    public function setDi(DiInterface $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * Returns the internal dependency injector
     *
     * @return DiInterface
     */
    public function getDi()
    {
        if (!is_object($this->dependencyInjector)) {
            return Di::getDefault();
        }

        return $this->dependencyInjector;
    }

    /**
     * Magic method __get
     *
     * @param string $propertyName
     *
     * @return mixed|null
     */
    public function __get($propertyName)
    {
        if ($this->getDi()->has($propertyName)) {
            return $this->getDi()->getShared($propertyName);
        }

        trigger_error(
            sprintf('Access to undefined property %s::%s', get_class($this->getDi()), $propertyName),
            E_WARNING
        );

        return null;
    }
}
