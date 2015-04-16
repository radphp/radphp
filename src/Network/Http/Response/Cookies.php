<?php

namespace Rad\Network\Http\Response;

use Rad\Network\Http\Cookie;

/**
 * Cookies
 *
 * This class is a bag to manage the cookies A cookies bag is automatically
 * registered as part of the ‘response’
 *
 * @package Rad\Network\Http\Response
 */
class Cookies implements CookiesInterface
{
    protected $encryption = false;

    /**
     * @var Cookie[]
     */
    protected $cookies = [];

    /**
     * Set if cookies in the bag must be automatically encrypted/decrypted
     *
     * @param bool $useEncryption
     *
     * @return Cookies
     */
    public function useEncryption($useEncryption)
    {
        $this->encryption = $useEncryption;

        return $this;
    }

    /**
     * Returns if the bag is automatically encrypting/decrypting cookies
     *
     * @return bool
     */
    public function isUsingEncryption()
    {
        return $this->encryption;
    }

    /**
     * Sets a cookie to be sent at the end of the request This method
     * overrides any cookie set before with the same name
     *
     * @param string $name     The name of the cookie.
     * @param string $value    The value of the cookie.
     * @param int    $expire   The time the cookie expires.
     * @param string $path     The path on the server in which the cookie will be available on.
     * @param string $domain   The domain that the cookie is available to.
     * @param bool   $secure   Indicates that the cookie should only be
     *                         transmitted over a secure HTTPS connection from the client.
     * @param bool   $httpOnly When TRUE the cookie will be made accessible only through the HTTP protocol.
     *
     * @return Cookies
     * @throws Cookie\Exception
     */
    public function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        if (!is_string($name)) {
            throw new Cookie\Exception('The cookie name must be string');
        }

        if (!isset($this->cookies[$name])) {
            $this->cookies[$name] = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
            $this->cookies[$name]->useEncryption($this->isUsingEncryption());
        } else {
            $this->cookies[$name]->setValue($value)
                ->setExpiration($expire)
                ->setPath($path)
                ->setDomain($domain)
                ->setSecure($secure)
                ->setHttpOnly($httpOnly);
        }

        return $this;
    }

    /**
     * Gets a cookie from the bag
     *
     * @param string $name
     *
     * @return null|Cookie
     * @throws Cookie\Exception
     */
    public function get($name)
    {
        if (!is_string($name)) {
            throw new Cookie\Exception('The cookie name must be string');
        }

        if (isset($this->cookies[$name])) {
            $this->cookies[$name]->useEncryption($this->isUsingEncryption());

            return $this->cookies[$name];
        }

        return null;
    }

    /**
     * Check if a cookie is defined in the bag or exists in the $_COOKIE superglobal
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        if (isset($this->cookies[$name]) || isset($_COOKIE[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Deletes a cookie by its name This method does not removes cookies from the $_COOKIE superglobal
     *
     * @param string $name
     *
     * @return bool
     */
    public function delete($name)
    {
        if ($this->cookies[$name]) {
            unset($this->cookies[$name]);

            return true;
        }

        return false;
    }

    /**
     * Sends the cookies to the client Cookies aren’t sent if headers are sent in the current request
     *
     * @return bool
     */
    public function send()
    {
        if (!headers_sent()) {
            foreach ($this->cookies as $cookie) {
                $cookie->send();
            }

            return true;
        }

        return false;
    }

    /**
     * Reset set cookies
     *
     * @return Cookies
     */
    public function reset()
    {
        $this->cookies = [];

        return $this;
    }
}
