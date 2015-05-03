<?php

namespace Rad\Database\Driver;

/**
 * Driver Interface
 *
 * @package Rad\Database\Driver
 */
interface DriverInterface
{
    /**
     * @return bool
     */
    public function connect();

    /**
     * @return void
     */
    public function disconnect();

    /**
     * @param array $config
     *
     * @return void
     */
    public function setConfig(array $config);
}
