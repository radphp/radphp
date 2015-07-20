<?php

namespace Rad\Network\Http;

use Rad\Network\Http\Message\ServerRequest;
use Rad\Network\Http\Request\UploadedFile;
use Rad\Network\Http\Message\Uri;
use Rad\Network\Http\Request\ReformatUploadedFiles;

/**
 * Http Request
 *
 * @package Rad\Network\Http
 */
class Request extends ServerRequest
{
    /**
     * Whether or not to trust HTTP_X headers set by most load balancers.
     * Only set to true if your application runs behind load balancers/proxies
     * that you control.
     *
     * @var bool
     */
    public $trustProxy = false;

    /**
     * Rad\Network\Http\Request constructor
     */
    public function __construct()
    {
        parent::__construct(
            $_SERVER,
            $this->prepareUploadedFiles($_FILES),
            $this->prepareUri($_SERVER),
            isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET',
            'php://input',
            $this->prepareHeaders($_SERVER)
        );

        $this->cookieParams = $_COOKIE;
        $this->queryParams = $_GET;
        $this->parsedBody = $this->prepareParsedBody();
    }

    /**
     * Gets variable from query params.
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
            if (isset($this->getQueryParams()[$name])) {
                if (empty($this->getQueryParams()[$name]) && $notAllowEmpty) {
                    return $defaultValue;
                }

                return $this->getQueryParams()[$name];
            }

            return $defaultValue;
        }

        return $this->getQueryParams();
    }

    /**
     * Gets variable from server params
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getServer($name)
    {
        if ($this->hasServer($name)) {
            return $this->getServerParams()[$name];
        }

        return null;
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
        return isset($this->getQueryParams()[$name]);
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
        return isset($this->getServerParams()[$name]);
    }

    /**
     * Checks whether request has been made using ajax.
     * Checks if $_SERVER[‘HTTP_X_REQUESTED_WITH’]==’XMLHttpRequest’
     *
     * @return bool
     */
    public function isAjax()
    {
        return ($this->getHeaderLine('X_REQUESTED_WITH') === 'XMLHttpRequest');
    }

    /**
     * Checks whether request has been made using any secure layer
     *
     * @return bool
     */
    public function isSecureRequest()
    {
        return ($this->getUri()->getScheme() === 'https');
    }

    /**
     * Gets active server address IP
     *
     * @return string
     */
    public function getServerAddress()
    {
        if ($this->getServer('SERVER_ADDR')) {
            return $this->getServer('SERVER_ADDR');
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
        if ($this->getServer('SERVER_NAME')) {
            return $this->getServer('SERVER_NAME');
        }

        return 'localhost';
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
        if ($this->getHeaderLine('X_FORWARDED_FOR') && $this->trustProxy) {
            $address = $this->getHeaderLine('X_FORWARDED_FOR');
        }

        if (is_null($address) && $this->getServer('REMOTE_ADDR')) {
            $address = $this->getServer('REMOTE_ADDR');
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
     * Gets HTTP user agent used to made the request
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->getHeaderLine('USER_AGENT');
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
     * Gets web page that refers active request. ie: http://www.google.com
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->getHeaderLine('REFERER');
    }

    /**
     * Gets array with mime/types and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT’]
     *
     * @return array
     */
    public function getAcceptableContent()
    {
        return $this->getQualityHeader('ACCEPT', 'accept');
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
        return $this->getQualityHeader('ACCEPT_CHARSET', 'charset');
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
        return $this->getQualityHeader('ACCEPT_LANGUAGE', 'language');
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
     * Get request media type
     *
     * @return string
     */
    public function getMediaType()
    {
        $contentType = $this->getServer('CONTENT_TYPE');
        if (!empty($contentType)) {
            if ($parts = explode(';', $contentType)) {
                return trim($parts[0]);
            }
        }

        return '';
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
        $httpServer = $this->getHeaderLine($serverIndex);
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

    /**
     * Prepare uploaded files
     *
     * @param $files
     *
     * @return array
     */
    protected function prepareUploadedFiles($files)
    {
        $fileKeys = ['error', 'name', 'size', 'tmp_name', 'type'];
        $reformatFiles = new ReformatUploadedFiles($files);

        $output = [];
        foreach ($reformatFiles as $key => $value) {
            $keys = array_keys($value);
            sort($keys);

            if ($fileKeys == $keys && is_array($value['tmp_name'])) {
                $output[$key] = $this->prepareUploadedFiles($reformatFiles[$key]);
                continue;
            }

            $output[$key] = new UploadedFile(
                $value['tmp_name'],
                $value['error'],
                $value['name'],
                $value['type'],
                $value['size']
            );
        }

        return $output;
    }

    /**
     * Returns the available headers in the request
     *
     * @param array $server
     *
     * @return array
     */
    protected function prepareHeaders($server)
    {
        $output = [];

        if (!is_array($server)) {
            return $output;
        }

        foreach ($server as $key => $value) {
            if (is_string($key) && strlen($key) > 5 && strpos($key, 'HTTP_') !== false) {
                $output[substr($key, 5)] = $value;
            }
        }

        return $output;
    }

    /**
     * Prepare Uri
     *
     * @param $server
     *
     * @return \Psr\Http\Message\UriInterface|Uri
     */
    protected function prepareUri($server)
    {
        $uri = new Uri('');

        if ((isset($server['HTTPS']) && $server['HTTPS'] != 'off') ||
            (isset($server['HTTP_X_FORWARDED_PROTO']) && $server['HTTP_X_FORWARDED_PROTO'] == 'https')
        ) {
            $schema = 'https';
        } else {
            $schema = 'http';
        }

        $parsedUrl = parse_url($server['REQUEST_URI']);
        $username = isset($server['PHP_AUTH_USER']) ? $server['PHP_AUTH_USER'] : null;
        $password = isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : null;

        return $uri->withScheme($schema)
            ->withHost($server['HTTP_HOST'])
            ->withPort($server['SERVER_PORT'])
            ->withPath(isset($parsedUrl['path']) ? $parsedUrl['path'] : '/')
            ->withUserInfo($username, $password)
            ->withQuery(isset($parsedUrl['query']) ? $parsedUrl['query'] : '');
    }

    /**
     * Prepare parsed body
     *
     * @return mixed|\SimpleXMLElement
     */
    protected function prepareParsedBody()
    {
        if ($this->getMethod() == self::METHOD_POST &&
            in_array($this->getMediaType(), ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            return $_POST;
        } else {
            $body = (string)$this->getBody();

            switch ($this->getMediaType()) {
                case 'application/json':
                    return json_decode($body, true);

                case 'application/xml':
                    return simplexml_load_string($body);

                default:
                    parse_str($body, $output);

                    return $output;
            }
        }
    }
}
