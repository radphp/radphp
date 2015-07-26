<?php

namespace Rad\Network\Http;

/**
 * Provide OO wrappers to manage a HTTP cookie
 *
 * @package Rad\Network\Http
 */
class Cookie
{
    /**
     * The name of the cookie.
     *
     * @var string
     */
    protected $name;

    /**
     * The value of the cookie.
     *
     * @var string
     */
    protected $value;

    /**
     * The time the cookie expires.
     *
     * @var int
     */
    protected $expire;

    /**
     * The path on the server in which the cookie will be available on.
     *
     * @var string
     */
    protected $path;

    /**
     * The domain that the cookie is available to.
     *
     * @var string
     */
    protected $domain;

    /**
     * Indicates that the cookie should only be transmitted over
     * a secure HTTPS connection from the client.
     *
     * @var bool
     */
    protected $secure;

    /**
     * When TRUE the cookie will be made accessible only through the HTTP protocol.
     *
     * @var bool
     */
    protected $httpOnly;

    /**
     * @var bool
     */
    protected $useEncryption = false;

    /**
     * @var bool
     */
    protected $read = false;

    public $cipher = MCRYPT_RIJNDAEL_256;
    public $cipherMode = MCRYPT_MODE_ECB;
    public $secretKey = 'This is my secret key';

    /**
     * Cookie constructor
     *
     * @param string $name     The name of the cookie.
     * @param string $value    The value of the cookie.
     * @param int    $expire   The time the cookie expires.
     * @param string $path     The path on the server in which the cookie will be available on.
     * @param string $domain   The domain that the cookie is available to.
     * @param bool   $secure   Indicates that the cookie should only be
     *                         transmitted over a secure HTTPS connection from the client.
     * @param bool   $httpOnly When TRUE the cookie will be made accessible only through the HTTP protocol.
     */
    public function __construct(
        $name,
        $value = '',
        $expire = 0,
        $path = '/',
        $domain = '',
        $secure = false,
        $httpOnly = false
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->expire = $expire;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    /**
     * Sets the cookie’s value
     *
     * @param string $value
     *
     * @return Cookie
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->read = true;

        return $this;
    }

    /**
     * Returns the cookie’s value
     *
     * @param string $defaultValue
     *
     * @return string
     */
    public function getValue($defaultValue = null)
    {
        if ($this->read === false) {
            if (isset($_COOKIE[$this->name])) {
                if ($this->useEncryption === true) {
                    return $this->value = $this->decrypt($_COOKIE[$this->name]);
                }

                return $this->value = $_COOKIE[$this->name];
            }

            return $defaultValue;
        }

        return $this->value;
    }

    /**
     * Sends the cookie to the HTTP client Stores the cookie definition in session
     *
     * @return Cookie
     */
    public function send()
    {
        if ($this->useEncryption && !empty($this->value)) {
            $value = $this->encrypt($this->value);
        } else {
            $value = $this->value;
        }

        setcookie($this->name, $value, $this->expire, $this->path, $this->domain, $this->secure, $this->httpOnly);

        return $this;
    }

    /**
     * Deletes the cookie by setting an expire time in the past
     *
     * @return bool
     */
    public function delete()
    {
        return setcookie(
            $this->name,
            null,
            1,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
    }

    /**
     * Sets if the cookie must be encrypted/decrypted automatically
     *
     * @param $useEncryption
     *
     * @return Cookie
     */
    public function useEncryption($useEncryption)
    {
        $this->useEncryption = $useEncryption;

        return $this;
    }

    /**
     * Check if the cookie is using implicit encryption
     *
     * @return bool
     */
    public function isUsingEncryption()
    {
        return $this->useEncryption;
    }

    /**
     * Sets the cookie’s expiration time
     *
     * @param $expire
     *
     * @return Cookie
     */
    public function setExpiration($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * Returns the current expiration time
     *
     * @return int
     */
    public function getExpiration()
    {
        return $this->expire;
    }

    /**
     * Sets the cookie’s expiration time
     *
     * @param string $path
     *
     * @return Cookie
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the current cookie’s path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the domain that the cookie is available to
     *
     * @param string $domain The domain that the cookie is available to.
     *
     * @return Cookie
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Returns the domain that the cookie is available to
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets if the cookie must only be sent when the connection is secure (HTTPS)
     *
     * @param bool $secure
     *
     * @return Cookie
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Returns whether the cookie must only be sent when the connection is secure (HTTPS)
     *
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * Sets if the cookie is accessible only through the HTTP protocol
     *
     * @param bool $httpOnly
     *
     * @return Cookie
     */
    public function setHttpOnly($httpOnly)
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * Returns if the cookie is accessible only through the HTTP protocol
     *
     * @return bool
     */
    public function getHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Magic __toString method converts the cookie's value to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

    /**
     * Encrypt cookie value
     *
     * @param string $string
     *
     * @return string
     */
    protected function encrypt($string)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->cipherMode), MCRYPT_RAND);

        return mcrypt_encrypt($this->cipher, $this->secretKey, $string, $this->cipherMode, $iv);
    }

    /**
     * Decrypt cookie value
     *
     * @param string $encryptedString
     *
     * @return string
     */
    protected function decrypt($encryptedString)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->cipherMode), MCRYPT_RAND);

        return mcrypt_decrypt($this->cipher, $this->secretKey, $encryptedString, $this->cipherMode, $iv);
    }
}
