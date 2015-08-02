<?php

namespace Rad\Authentication\Provider;

use Rad\Authentication\AbstractAuthenticationProvider;

/**
 * Simple Authentication
 *
 * @package Rad\Authentication\Provider
 */
class SimpleAuthentication extends AbstractAuthenticationProvider
{
    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        return $this->repository->findUser($this->identity, $this->credential);
    }
}
