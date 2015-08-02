<?php

namespace Rad\Cryptography\Password;

use InvalidArgumentException;
use Rad\Cryptography\PasswordInterface;

/**
 * Default Password
 *
 * @package Rad\Cryptography\Password
 */
class DefaultPassword implements PasswordInterface
{
    protected $algorithm;
    protected $options;

    /**
     * Rad\Cryptography\Password\DefaultPassword constructor
     *
     * @param int   $algorithm Password algorithm
     * @param array $options   Password options
     */
    public function __construct($algorithm = PASSWORD_DEFAULT, array $options = [])
    {
        if (!in_array($algorithm, [PASSWORD_DEFAULT, PASSWORD_BCRYPT])) {
            throw new InvalidArgumentException('Invalid algorithm.');
        }

        $this->algorithm = $algorithm;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function hash($password)
    {
        return password_hash($password, $this->algorithm, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Checks if the given hash matches the given options.
     *
     * @param string $hash Password hash
     *
     * @return string
     */
    public function needsRehash($hash)
    {
        return password_needs_rehash($hash, $this->algorithm, $this->options);
    }

    /**
     * Returns information about the given hash
     *
     * @param string $hash Password hash
     *
     * @return array
     */
    public function getInfo($hash)
    {
        return password_get_info($hash);
    }
}
