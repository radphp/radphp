<?php

namespace Rad\DependencyInjection;

/**
 * ContainerAware Interface
 *
 * @package Rad\DependencyInjection
 */
interface ContainerAwareInterface
{
    /**
     * Set container
     *
     * @param Container $container
     */
    public function setContainer(Container $container);

    /**
     * Get container
     *
     * @return Container
     */
    public function getContainer();
}
