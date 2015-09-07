<?php

namespace Rad\Core;

/**
 * Bundle Interface
 *
 * @package Rad\Core
 */
interface BundleInterface
{
    /**
     * Startup bundle
     *
     * @return void
     */
    public function startup();

    /**
     * Load bundle config
     *
     * @return void
     */
    public function loadConfig();

    /**
     * Load bundle service
     *
     * @return void
     */
    public function loadService();

    /**
     * Get bundle name
     *
     * @return string
     */
    public function getName();

    /**
     * Get bundle namespace
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Get bundle path
     *
     * @return string
     */
    public function getPath();
}
