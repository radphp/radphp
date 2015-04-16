<?php

namespace Rad\Network\Http\Response;

use Rad\Network\Http\Cookie;

/**
 * Cookies Interface
 *
 * @package Rad\Network\Http\Response
 */
interface CookiesInterface
{
    /**
     * Set if cookies in the bag must be automatically encrypted/decrypted
     *
     * @param bool $useEncryption
     *
     * @return Cookies
     */
    public function useEncryption($useEncryption);

    /**
     * Returns if the bag is automatically encrypting/decrypting cookies
     *
     * @return bool
     */
    public function isUsingEncryption();

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
     */
    public function set($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false);

    /**
     * Gets a cookie from the bag
     *
     * @param string $name
     *
     * @return Cookie
     */
    public function get($name);

    /**
     * Check if a cookie is defined in the bag or exists in the $_COOKIE superglobal
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Deletes a cookie by its name This method does not removes cookies from the $_COOKIE superglobal
     *
     * @param string $name
     *
     * @return bool
     */
    public function delete($name);

    /**
     * Sends the cookies to the client Cookies aren’t sent if headers are sent in the current request
     *
     * @return bool
     */
    public function send();

    /**
     * Reset set cookies
     *
     * @return Cookies
     */
    public function reset();
}
