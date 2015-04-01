<?php

namespace Rad\Core;

/**
 * Singleton trait
 *
 * @package Rad\Core\Pattern
 */
trait SingletonTrait
{
    protected static $instance;

    /**
     * Get instance
     *
     * @return static
     */
    final public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    final private function __construct()
    {
        $this->init();
    }

    /**
     * Init method for call in constructor
     */
    protected function init()
    {
    }

    /**
     * Ignore magic clone
     */
    final private function __clone()
    {
    }

    /**
     * Ignore sleep magic method
     */
    final private function __sleep()
    {
    }

    /**
     * Ignore wakeup magic method
     */
    final private function __wakeup()
    {
    }
}
