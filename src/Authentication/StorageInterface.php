<?php

namespace Rad\Authentication;

/**
 * Storage Interface
 *
 * @package Rad\Authentication
 */
interface StorageInterface
{
    /**
     * Read storage
     *
     * @return mixed
     */
    public function read();

    /**
     * Write into storage
     *
     * @param mixed $data Storage data
     */
    public function write($data);

    /**
     * Flush storage
     */
    public function flush();

    /**
     * Check data exist in storage
     *
     * @return bool
     */
    public function exist();
}
