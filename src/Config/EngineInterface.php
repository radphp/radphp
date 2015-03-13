<?php

namespace Rad\Config;

/**
 * Engine Interface
 *
 * @package Rad\Config
 */
interface EngineInterface
{
    /**
     * Load config
     *
     * @param mixed $config Config file
     *
     * @return array
     */
    public function load($config);

    /**
     * Dump config to file
     *
     * @param string $file Config write to this file
     * @param array  $data Config data
     *
     * @return bool
     */
    public function dump($file, array $data);
}
