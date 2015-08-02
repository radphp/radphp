<?php

namespace Rad\Authentication;

use Rad\Database\ConnectionManager;
use Rad\Authentication\Repository\DatabaseRepository;

/**
 * Auth
 *
 * @package Rad\Authentication
 */
class Auth
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Rad\Authentication\Auth constructor
     *
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Authenticate
     *
     * @param AbstractAuthenticationProvider $provider Authentication provider
     *
     * @return bool
     * @throws \Rad\Database\Exception
     */
    public function authenticate(AbstractAuthenticationProvider $provider)
    {
        if (null === $provider->getRepository()) {
            $provider->setRepository(new DatabaseRepository(ConnectionManager::get('default')));
        }

        $output = $provider->authenticate();

        $this->storage->flush();

        if (is_array($output)) {
            $this->storage->write($output);

            return true;
        }

        return $output;
    }

    /**
     * Get storage
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
