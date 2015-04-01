<?php

namespace Rad\DependencyInjection;

/**
 * Injection Aware Interface
 *
 * @package Rad\DependencyInjection
 */
interface InjectionAwareInterface
{
    /**
     * Sets the dependency injector
     *
     * @param DiInterface $dependencyInjector
     */
    public function setDi(DiInterface $dependencyInjector);

    /**
     * Returns the internal dependency injector
     *
     * @return DiInterface
     */
    public function getDi();
}
