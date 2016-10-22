<?php

namespace Rad\Configure;

/**
 * Dumper Interface
 *
 * @package Rad\Configure
 */
interface DumperInterface
{
    /**
     * Dump config
     *
     * @param array $data Config data
     *
     * @return bool Return true if operation's succeed otherwise return false.
     */
    public function dump(array $data);
}
