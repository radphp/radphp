<?php

namespace Rad\Authentication;

use Rad\Cryptography\PasswordInterface;
use Rad\Cryptography\Password\DefaultPassword;

/**
 * Abstract Repository
 *
 * @package Rad\Authentication
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var PasswordInterface
     */
    protected $passwordCrypt;

    /**
     * Rad\Authentication\AbstractRepository constructor
     *
     * @param PasswordInterface|null $passwordCrypt
     */
    public function __construct(PasswordInterface $passwordCrypt = null)
    {
        if (null === $passwordCrypt) {
            $passwordCrypt = new DefaultPassword();
        }

        $this->passwordCrypt = $passwordCrypt;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function findUser($identity, $credential);

    /**
     * Set password crypt
     *
     * @param PasswordInterface $passwordCrypt
     */
    public function setPasswordCrypt(PasswordInterface $passwordCrypt)
    {
        $this->passwordCrypt = $passwordCrypt;
    }
}
