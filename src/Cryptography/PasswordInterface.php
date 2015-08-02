<?php

namespace Rad\Cryptography;

/**
 * Password Interface
 *
 * @package Rad\Cryptography
 */
interface PasswordInterface
{
    /**
     * Hash password
     *
     * @param string $password Password phrase
     *
     * @return string|bool Returns the hashed password, or FALSE on failure.
     */
    public function hash($password);

    /**
     * Verify password
     *
     * @param string $password Password phrase
     * @param string $hash     Password hash
     *
     * @return bool
     */
    public function verify($password, $hash);
}
