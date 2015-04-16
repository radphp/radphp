<?php

namespace Rad\Network\Http;

use Rad\Network\Http\Request\File;

/**
 * Http Request
 *
 * @package Rad\Network\Http
 */
class Request implements RequestInterface
{
    /**
     * Whether or not to trust HTTP_X headers set by most load balancers.
     * Only set to true if your application runs behind load balancers/proxies
     * that you control.
     *
     * @var bool
     */
    public $trustProxy = false;

    protected $put;
    protected $rawBody;

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_HEAD = 'HEAD';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';

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
    public function get($name = null, $defaultValue = null, $notAllowEmpty = false)
    {
        if (!is_null($name)) {
            if (isset($_REQUEST[$name])) {
                if (empty($_REQUEST[$name]) && $notAllowEmpty) {
                    return $defaultValue;
                }

                return $_REQUEST[$name];
            }

            return $defaultValue;
        }

        return $_REQUEST;
    }

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
    public function getPost($name = null, $defaultValue = null, $notAllowEmpty = false)
    {
        if (!is_null($name)) {
            if (isset($_POST[$name])) {
                if (empty($_POST[$name]) && $notAllowEmpty) {
                    return $defaultValue;
                }

                return $_POST[$name];
            }

            return $defaultValue;
        }

        return $_POST;
    }

    /**
     * Gets a variable from put request
     *
     * @param string|null $name
     * @param mixed       $defaultValue
     * @param bool        $notAllowEmpty
     *
     * @return mixed
     */
    public function getPut($name = null, $defaultValue = null, $notAllowEmpty = false)
    {
        if ($this->isPut()) {
            if (!is_array($this->put)) {
                parse_str($this->getRawBody(), $put);
                $this->put = $put;
            }
        }

        if (!is_null($name)) {
            if (isset($this->put[$name])) {
                if (empty($this->put[$name]) && $notAllowEmpty === true) {
                    return $defaultValue;
                } else {
                    return $this->put[$name];
                }
            }

            return $defaultValue;
        }

        return $this->put;
    }

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
    public function getQuery($name = null, $defaultValue = null, $notAllowEmpty = false)
    {
        if (!is_null($name)) {
            if (isset($_GET[$name])) {
                if (empty($_GET[$name]) && $notAllowEmpty) {
                    return $defaultValue;
                }

                return $_GET[$name];
            }

            return $defaultValue;
        }

        return $_GET;
    }

    /**
     * Gets variable from $_SERVER super global
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getServer($name)
    {
        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return null;
    }

    /**
     * Checks whether $_REQUEST super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($_REQUEST[$name]);
    }

    /**
     * Checks whether $_POST super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPost($name)
    {
        return isset($_POST[$name]);
    }

    /**
     * Checks whether put has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasPut($name)
    {
        return isset($this->getPut()[$name]);
    }

    /**
     * Checks whether $_GET super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasQuery($name)
    {
        return isset($_GET[$name]);
    }

    /**
     * Checks whether $_SERVER super global has certain index
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasServer($name)
    {
        return isset($_SERVER[$name]);
    }

    /**
     * Gets HTTP header from request data
     *
     * @param string $header HTTP header
     *
     * @return string
     */
    public function getHeader($header)
    {
        if (isset($_SERVER[$header])) {
            return $_SERVER[$header];
        }

        if (isset($_SERVER['HTTP_' . $header])) {
            return $_SERVER['HTTP_' . $header];
        }

        return '';
    }

    /**
     * Gets HTTP schema (http/https)
     *
     * @return string
     */
    public function getScheme()
    {
        if ($this->trustProxy && $this->getServer('HTTP_X_FORWARDED_PROTO')) {
            return $this->getServer('HTTP_X_FORWARDED_PROTO');
        }

        if ($this->getServer('HTTPS')) {
            if (is_string($this->getServer('HTTPS')) && $this->getServer('HTTPS') == 'off') {
                return 'http';
            } else {
                return 'https';
            }
        } else {
            return 'http';
        }
    }

    /**
     * Checks whether request has been made using ajax.
     * Checks if $_SERVER[‘HTTP_X_REQUESTED_WITH’]==’XMLHttpRequest’
     *
     * @return bool
     */
    public function isAjax()
    {
        return ($this->getHeader('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
    }

    /**
     * Checks whether request has been made using any secure layer
     *
     * @return bool
     */
    public function isSecureRequest()
    {
        return ($this->getScheme() === 'https');
    }

    /**
     * Gets HTTP raw request body
     *
     * @return string
     */
    public function getRawBody()
    {
        if (is_string($this->rawBody)) {
            return $this->rawBody;
        }

        $stream = fopen('php://input', 'rb');
        $tempStream = fopen("php://temp", "w+b");
        $len = stream_copy_to_stream($stream, $tempStream);

        if ($stream === false) {
            return false;
        }

        if ($len > 0) {
            $this->rawBody = stream_get_contents($stream);
        } elseif (!$len) {
            $this->rawBody = '';
        } else {
            return false;
        }

        fclose($stream);
        fclose($tempStream);

        return $this->rawBody;
    }

    /**
     * Gets decoded JSON HTTP raw request body
     *
     * @param bool $assoc
     *
     * @return array|mixed
     */
    public function getJsonRawBody($assoc = false)
    {
        if (is_string($rawBody = $this->getRawBody())) {
            return json_decode($rawBody, $assoc);
        }

        return [];
    }

    /**
     * Gets active server address IP
     *
     * @return string
     */
    public function getServerAddress()
    {
        if (isset($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }

        return '127.0.0.1';
    }

    /**
     * Gets active server name
     *
     * @return string
     */
    public function getServerName()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        return 'localhost';
    }

    /**
     * Gets information about schema, host and port used by the request
     *
     * @return string
     */
    public function getHttpHost()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        $name = $this->getServer('SERVER_NAME');
        $port = $this->getServer('SERVER_PORT');
        $schema = $this->getScheme();

        // Request is standard http or is standard a secure http return SERVER_NAME
        if ((($port == '80') && ($schema == 'http')) || (($port == '443') && ($schema == 'https'))) {
            return $name;
        }

        return $name . ':' . $port;
    }

    /**
     * Gets most possible client IPv4 Address.
     * This method search in $_SERVER[‘REMOTE_ADDR’] and optionally in $_SERVER[‘HTTP_X_FORWARDED_FOR’]
     *
     * @return string
     */
    public function getClientAddress()
    {
        $address = null;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $this->trustProxy) {
            $address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (is_null($address) && isset($_SERVER['REMOTE_ADDR'])) {
            $address = $_SERVER['REMOTE_ADDR'];
        }

        if (is_string($address)) {
            if ($addresses = explode(',', $address)) {
                return $addresses[0];
            }
            return $address;
        }

        return false;
    }

    /**
     * Gets HTTP method which request has been made
     *
     * @return string
     */
    public function getMethod()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return $_SERVER['REQUEST_METHOD'];
        }

        return '';
    }

    /**
     * Gets HTTP URI which request has been made
     *
     * @return string
     */
    public function getURI()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            return $_SERVER['REQUEST_URI'];
        }

        return '';
    }

    /**
     * Gets HTTP user agent used to made the request
     *
     * @return string
     */
    public function getUserAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        return '';
    }

    /**
     * Check if HTTP method match any of the passed methods
     *
     * @param string|array $methods
     *
     * @return bool
     */
    public function isMethod($methods)
    {
        if (is_array($methods)) {
            return in_array($this->getMethod(), $methods);
        }

        if (is_string($methods)) {
            return ($this->getMethod() == $methods);
        }

        return false;
    }

    /**
     * Checks whether HTTP method is POST.
     *
     * @return bool
     */
    public function isPost()
    {
        return ($this->getMethod() == self::METHOD_POST);
    }

    /**
     * Checks whether HTTP method is GET.
     *
     * @return bool
     */
    public function isGet()
    {
        return ($this->getMethod() == self::METHOD_GET);
    }

    /**
     * Checks whether HTTP method is PUT.
     *
     * @return bool
     */
    public function isPut()
    {
        return ($this->getMethod() == self::METHOD_PUT);
    }

    /**
     * Checks whether HTTP method is PATCH.
     *
     * @return bool
     */
    public function isPatch()
    {
        return ($this->getMethod() == self::METHOD_PATCH);
    }

    /**
     * Checks whether HTTP method is HEAD.
     *
     * @return bool
     */
    public function isHead()
    {
        return ($this->getMethod() == self::METHOD_HEAD);
    }

    /**
     * Checks whether HTTP method is DELETE.
     *
     * @return bool
     */
    public function isDelete()
    {
        return ($this->getMethod() == self::METHOD_DELETE);
    }

    /**
     * Checks whether HTTP method is OPTIONS.
     *
     * @return bool
     */
    public function isOptions()
    {
        return ($this->getMethod() == self::METHOD_OPTIONS);
    }

    /**
     * Checks whether request includes attached files
     *
     * @return bool
     */
    public function hasFiles()
    {
        if (!is_array($_FILES)) {
            return false;
        }

        foreach ($_FILES as $fieldName => $file) {
            if (isset($file['tmp_name'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets attached files as Rad\Network\Http\Request\File instance
     *
     * @return File[]
     */
    public function getUploadedFiles()
    {
        $output = [];

        foreach ($_FILES as $fieldName => $file) {
            // When multiple files upload
            if (is_array($file['name'])) {
                for ($index = 0; $index <= (count($file['name']) - 1); $index++) {
                    $output[] = new File($fieldName, [
                        'name' => $file['name'][$index],
                        'type' => $file['type'][$index],
                        'tmp_name' => $file['tmp_name'][$index],
                        'error' => $file['error'][$index],
                        'size' => $file['size'][$index]
                    ]);
                }
            } else {
                $output[] = new File($fieldName, $file);
            }
        }

        return $output;
    }

    /**
     * Returns the available headers in the request
     *
     * @return array
     */
    public function getHeaders()
    {
        $output = [];

        if (!is_array($_SERVER)) {
            return $output;
        }

        foreach ($_SERVER as $key => $value) {
            if (is_string($key) && strlen($key) > 5 && strpos($key, 'HTTP_') !== false) {
                $output[substr($key, 5)] = $value;
            }
        }

        return $output;
    }

    /**
     * Gets web page that refers active request. ie: http://www.google.com
     *
     * @return string
     */
    public function getReferer()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }

        return '';
    }

    /**
     * Gets array with mime/types and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT’]
     *
     * @return array
     */
    public function getAcceptableContent()
    {
        return $this->getQualityHeader('HTTP_ACCEPT', 'accept');
    }

    /**
     * Gets preferred mime/type accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT’]
     *
     * @return array
     */
    public function getPreferredAccept()
    {
        return $this->getPreferredQuality($this->getAcceptableContent(), 'accept');
    }

    /**
     * Gets charsets array and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_CHARSET’]
     *
     * @return array
     */
    public function getClientCharsets()
    {
        return $this->getQualityHeader('HTTP_ACCEPT_CHARSET', 'charset');
    }

    /**
     * Gets preferred charset accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_CHARSET’]
     *
     * @return string
     */
    public function getPreferredCharset()
    {
        return $this->getPreferredQuality($this->getClientCharsets(), 'charset');
    }

    /**
     * Gets languages array and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_LANGUAGE’]
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->getQualityHeader('HTTP_ACCEPT_LANGUAGE', 'language');
    }

    /**
     * Gets preferred language accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_LANGUAGE’]
     *
     * @return string
     */
    public function getPreferredLanguage()
    {
        return $this->getPreferredQuality($this->getLanguages(), 'language');
    }

    /**
     * Get request content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getServer('CONTENT_TYPE');
    }

    /**
     * Process a request header and return an array of values with their qualities
     *
     * @param string $serverIndex
     * @param string $name
     *
     * @return array
     */
    protected function getQualityHeader($serverIndex, $name)
    {
        $httpServer = $this->getServer($serverIndex);
        $parts = preg_split('/,\\s*/', $httpServer);

        $output = [];
        foreach ($parts as $part) {
            $headerParts = explode(';', $part);
            if (isset($headerParts[1])) {
                $qualityPart = $headerParts[1];
                $qVal = substr($qualityPart, 2);
                $quality = ($qVal == (int)$qVal) ? (int)$qVal : (float)$qVal;
            } else {
                $quality = 1;
            }

            $headerName = $headerParts[0];
            $output[] = [$name => $headerName, 'quality' => $quality];
        }

        return $output;
    }

    /**
     * Process a request header and return the one with preferred quality
     *
     * @param array  $qualityParts
     * @param string $name
     *
     * @return string
     */
    protected function getPreferredQuality(array $qualityParts, $name)
    {
        $i = 0;
        $quality = 0;
        $selectedName = '';

        foreach ($qualityParts as $accept) {
            if ($i == 0) {
                $quality = $accept['quality'];
                $selectedName = $accept[$name];
            } else {
                $acceptQuality = $accept['quality'];
                $preferredQuality = ($quality < $acceptQuality);

                if ($preferredQuality === true) {
                    $quality = $acceptQuality;
                    $selectedName = $accept[$name];
                }
            }
            $i++;
        }

        return $selectedName;
    }
}
