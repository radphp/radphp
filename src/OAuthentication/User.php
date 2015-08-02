<?php

namespace Rad\OAuthentication;

use ArrayAccess;

/**
 * User Detail
 *
 * @package Rad\OAuthentication
 */
class User implements ArrayAccess
{
    protected $id;
    protected $username = '';
    protected $name = '';
    protected $email = '';
    protected $avatarUri = '';
    protected $container = [];

    /**
     * Rad\OAuthentication\User constructor
     *
     * @param string|array $data
     *
     * @throws Exception
     */
    public function __construct($data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (is_array($data)) {
            $this->container = $data;
        } else {
            throw new Exception('User data must be json string or array.');
        }
    }

    /**
     * Set user identifier
     *
     * @param string|int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get user identifier
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = (string)$username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = (string)$email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set avatar uri
     *
     * @param string $avatarUri
     *
     * @return self
     */
    public function setAvatarUri($avatarUri)
    {
        $this->avatarUri = (string)$avatarUri;

        return $this;
    }

    /**
     * Get avatar uri
     *
     * @return string
     */
    public function getAvatarUri()
    {
        return $this->avatarUri;
    }

    /**
     * Get user detail
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->container)) {
            return $this->container[$key];
        }

        return null;
    }

    /**
     * Check user detail is exist
     *
     * @param string $key
     *
     * @return bool
     */
    public function exist($key)
    {
        return array_key_exists($key, $this->container);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @return boolean true on success or false on failure.
     *                  The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->exist($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @throws Exception
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception('Can not set value');
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     *
     * @throws Exception
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        throw new Exception('Can not unset value');
    }
}
