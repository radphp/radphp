<?php

namespace Rad\Configure;

/**
 * Loader Interface
 *
 * @package Rad\Configure
 */
interface LoaderInterface
{
    /**
     * Load config
     *
     * @return array
     */
    public function load();
}
