<?php

namespace Rad\Authentication;

/**
 * Authentication Provider Interface
 *
 * @package Rad\Authentication
 */
interface AuthenticationProviderInterface
{
    /**
     * Authenticate
     *
     * @return bool|array Return false when user not authenticate, return array on authenticated
     */
    public function authenticate();
}
