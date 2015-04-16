<?php

namespace Rad\Network\Http;

use Rad\Network\Http\Request\File;

/**
 * Request Interface
 *
 * @package Rad\Network\Http
 */
interface RequestInterface
{
    /**
     * Gets a variable from the $_REQUEST super global.
     * If no parameters are given the $_REQUEST super global is returned
     *
     * @param string $name
     * @param mixed  $defaultValue
     * @param bool   $notAllowEmpty
     *
     * @return mixed
     */
    public function get($name = null, $defaultValue = null, $notAllowEmpty = false);

    /**
     * Gets a variable from the $_POST super global.
     * If no parameters are given the $_POST super global is returned
     *
     * @param string $name
     * @param mixed  $defaultValue
     * @param bool   $notAllowEmpty
     *
     * @return mixed
     */
    public function getPost($name = null, $defaultValue = null, $notAllowEmpty = false);

    /**
     * Gets a variable from put request
     *
     * @param string|null $name
     * @param mixed       $defaultValue
     * @param bool        $notAllowEmpty
     *
     * @return mixed
     */
    public function getPut($name = null, $defaultValue = null, $notAllowEmpty = false);

    /**
     * Gets variable from $_GET super global.
     * If no parameters are given the $_GET super global is returned
     *
     * @param string $name
     * @param mixed  $defaultValue
     * @param bool   $notAllowEmpty
     *
     * @return mixed
     */
    public function getQuery($name = null, $defaultValue = null, $notAllowEmpty = false);

    /**
     * Gets variable from $_SERVER super global
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getServer($name);

    /**
     * Checks whether $_REQUEST super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * Checks whether $_POST super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPost($name);

    /**
     * Checks whether put has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPut($name);

    /**
     * Checks whether $_GET super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasQuery($name);

    /**
     * Checks whether $_SERVER super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasServer($name);

    /**
     * Gets HTTP header from request data
     *
     * @param string $header HTTP header
     *
     * @return string
     */
    public function getHeader($header);

    /**
     * Gets HTTP schema (http/https)
     *
     * @return string
     */
    public function getScheme();

    /**
     * Checks whether request has been made using ajax.
     * Checks if $_SERVER[‘HTTP_X_REQUESTED_WITH’]==’XMLHttpRequest’
     *
     * @return bool
     */
    public function isAjax();

    /**
     * Checks whether request has been made using any secure layer
     *
     * @return bool
     */
    public function isSecureRequest();

    /**
     * Gets HTTP raw request body
     *
     * @return string
     */
    public function getRawBody();

    /**
     * Gets active server address IP
     *
     * @return string
     */
    public function getServerAddress();

    /**
     * Gets active server name
     *
     * @return string
     */
    public function getServerName();

    /**
     * Gets information about schema, host and port used by the request
     *
     * @return string
     */
    public function getHttpHost();

    /**
     * Gets most possible client IPv4 Address.
     * This method search in $_SERVER[‘REMOTE_ADDR’] and optionally in $_SERVER[‘HTTP_X_FORWARDED_FOR’]
     *
     * @return string
     */
    public function getClientAddress();

    /**
     * Gets HTTP method which request has been made
     *
     * @return string
     */
    public function getMethod();

    /**
     * Gets HTTP user agent used to made the request
     *
     * @return string
     */
    public function getUserAgent();

    /**
     * Check if HTTP method match any of the passed methods
     *
     * @param string|array $methods
     *
     * @return bool
     */
    public function isMethod($methods);

    /**
     * Checks whether HTTP method is POST.
     *
     * @return bool
     */
    public function isPost();

    /**
     * Checks whether HTTP method is GET.
     *
     * @return bool
     */
    public function isGet();

    /**
     * Checks whether HTTP method is PUT.
     *
     * @return bool
     */
    public function isPut();

    /**
     * Checks whether HTTP method is HEAD.
     *
     * @return bool
     */
    public function isHead();

    /**
     * Checks whether HTTP method is DELETE.
     *
     * @return bool
     */
    public function isDelete();

    /**
     * Checks whether HTTP method is OPTIONS.
     *
     * @return bool
     */
    public function isOptions();

    /**
     * Checks whether request includes attached files
     *
     * @return bool
     */
    public function hasFiles();

    /**
     * Gets attached files as Rad\Network\Http\Request\File instance
     *
     * @return File[]
     */
    public function getUploadedFiles();

    /**
     * Gets web page that refers active request. ie: http://www.google.com
     *
     * @return string|null
     */
    public function getReferer();

    /**
     * Gets array with mime/types and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT’]
     *
     * @return array
     */
    public function getAcceptableContent();

    /**
     * Gets preferred mime/type accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT’]
     *
     * @return array
     */
    public function getPreferredAccept();

    /**
     * Gets charsets array and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_CHARSET’]
     *
     * @return array
     */
    public function getClientCharsets();

    /**
     * Gets preferred charset accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_CHARSET’]
     *
     * @return string
     */
    public function getPreferredCharset();

    /**
     * Gets languages array and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_LANGUAGE’]
     *
     * @return array
     */
    public function getLanguages();

    /**
     * Gets preferred language accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_LANGUAGE’]
     *
     * @return string
     */
    public function getPreferredLanguage();
}
