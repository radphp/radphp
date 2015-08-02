<?php

namespace Rad\Authentication;

use InvalidArgumentException;

/**
 * Abstract Authentication Provider
 *
 * @package Rad\Authentication
 */
abstract class AbstractAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var string|null
     */
    protected $identity;

    /**
     * @var string|null
     */
    protected $credential;

    /**
     * Rad\Authentication\AbstractAuthenticationProvider
     *
     * @param string|null $identity
     * @param string|null $credential
     */
    public function __construct($identity = null, $credential = null)
    {
        if (null !== $identity && !is_string($identity)) {
            throw new InvalidArgumentException('Identity must be null or string.');
        }

        if (null !== $credential && !is_string($credential)) {
            throw new InvalidArgumentException('Credential must be null or string.');
        }

        $this->identity = $identity;
        $this->credential = $credential;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function authenticate();

    /**
     * Set repository
     *
     * @param RepositoryInterface $repository
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get repository
     *
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set identity
     *
     * @param null|string $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    /**
     * Get identity
     *
     * @return null|string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Set credential
     *
     * @param null|string $credential
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
    }

    /**
     * Get credential
     *
     * @return null|string
     */
    public function getCredential()
    {
        return $this->credential;
    }
}
