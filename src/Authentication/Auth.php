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
     * @var UserDetails
     */
    protected $userDetails;

    /**
     * Rad\Authentication\Auth constructor
     *
     * @param StorageInterface $storage
     * @param UserDetails      $userDetails
     */
    public function __construct(StorageInterface $storage, UserDetails $userDetails = null)
    {
        $this->storage = $storage;
        $this->userDetails = $userDetails;
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
            if (null !== $this->userDetails) {
                $output = $this->userDetails->getDetails($output);
            }

            $this->storage->write($output);

            return true;
        }

        return $output;
    }

    /**
     * Is authenticated
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->getStorage()->exist();
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
