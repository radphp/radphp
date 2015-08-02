<?php

namespace Rad\Authentication;

/**
 * Repository Interface
 *
 * @package Rad\Authentication
 */
interface RepositoryInterface
{
    /**
     * Find user
     *
     * @param string $identity
     * @param string $credential
     *
     * @return array
     */
    public function findUser($identity, $credential);
}
