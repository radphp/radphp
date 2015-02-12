<?php

namespace Rad\Http;

use Rad\Http\Request\File;

/**
 * Http Request
 *
 * @package Rad\Http
 */
class Request implements RequestInterface
{
    /**
     * Which request method was used to access the page; i.e. 'GET', 'HEAD', 'POST', 'PUT'.
     * @var string
     */
    protected $method;
    protected $uri;
    protected $serverAddress;
    protected $serverName;
    protected $httpHost;
    protected $input;
    protected $body;
    protected $jsonBody;

    /**
     * Whether or not to trust HTTP_X headers set by most load balancers.
     * Only set to true if your application runs behind load balancers/proxies
     * that you control.
     * @var bool
     */
    public $trustProxy = false;
    private $superGlobals = [];

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_HEAD = 'HEAD';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * Http request constructor
     */
    public function __construct()
    {
        $this->superGlobals = [
            'post' => &$_POST,
            'query' => &$_GET,
            'request' => &$_REQUEST,
            'server' => &$_SERVER,
            'environment' => &$_ENV,
            'files' => &$_FILES
        ];

        // unset all parameters to remove regular access to them
        unset($_GET, $_POST, $_REQUEST, $_SERVER, $_ENV, $_FILES);

        $this->method = $this->superGlobals['server']['REQUEST_METHOD'];
        $this->uri = $this->superGlobals['server']['REQUEST_URI'];
        $this->serverAddress = $this->superGlobals['server']['SERVER_ADDR'];
        $this->serverName = $this->superGlobals['server']['SERVER_NAME'];
        $this->httpHost = $this->superGlobals['server']['HTTP_HOST'];
        $this->input = $this->prepareInput();
    }

    /**
     * Gets a variable from the $_REQUEST super global.
     * If no parameters are given the $_REQUEST super global is returned
     *
     * @param string|null $name
     * @param mixed       $defaultValue
     *
     * @return mixed
     */
    public function get($name = null, $defaultValue = null)
    {
        if (array_key_exists($name, $this->superGlobals['request'])) {
            return $this->superGlobals['request'][$name];
        } elseif ($defaultValue) {
            return $defaultValue;
        }

        return $this->superGlobals['request'];
    }

    /**
     * Gets a variable from the $_POST super global.
     * If no parameters are given the $_POST super global is returned
     *
     * @param string|null $name
     * @param mixed       $defaultValue
     *
     * @return mixed
     */
    public function getPost($name = null, $defaultValue = null)
    {
        if (array_key_exists($name, $this->superGlobals['post'])) {
            return $this->superGlobals['post'][$name];
        } elseif ($defaultValue) {
            return $defaultValue;
        }

        return $this->superGlobals['post'];
    }

    /**
     * Gets a variable from put request
     *
     * @param string|null $name
     * @param mixed       $defaultValue
     *
     * @return mixed
     */
    public function getPut($name = null, $defaultValue = null)
    {
        if (array_key_exists($name, $this->input)) {
            return $this->input[$name];
        } elseif ($defaultValue) {
            return $defaultValue;
        }

        return $this->input;
    }

    /**
     * Gets variable from $_GET super global.
     * If no parameters are given the $_GET super global is returned
     *
     * @param string|null $name
     * @param mixed       $defaultValue
     *
     * @return mixed
     */
    public function getQuery($name = null, $defaultValue = null)
    {
        if (array_key_exists($name, $this->superGlobals['query'])) {
            return $this->superGlobals['query'][$name];
        } elseif ($defaultValue) {
            return $defaultValue;
        }

        return $this->superGlobals['query'];
    }

    /**
     * Gets variable from $_SERVER super global
     *
     * @param string $name
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function getServer($name = null, $defaultValue = null)
    {
        if (array_key_exists($name, $this->superGlobals['server'])) {
            return $this->superGlobals['server'][$name];
        } elseif ($defaultValue) {
            return $defaultValue;
        }

        return $this->superGlobals['server'];
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
        return array_key_exists($name, $this->superGlobals['request']);
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
        return array_key_exists($name, $this->superGlobals['post']);
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
        return array_key_exists($name, $this->input);
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
        return array_key_exists($name, $this->superGlobals['query']);
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
        return array_key_exists($name, $this->superGlobals['server']);
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
        //TODO: Implement getHeader() method
    }

    /**
     * Gets HTTP schema (http/https)
     * @return string
     */
    public function getScheme()
    {
        if ($this->trustProxy && getenv('HTTP_X_FORWARDED_PROTO')) {
            return getenv('HTTP_X_FORWARDED_PROTO');
        }

        return getenv('HTTPS') ? 'https' : 'http';
    }

    /**
     * Checks whether request has been made using ajax.
     * Checks if $_SERVER[‘HTTP_X_REQUESTED_WITH’]==’XMLHttpRequest’
     * @return bool
     */
    public function isAjax()
    {
        return (getenv('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    /**
     * Checks whether request has been made using SOAP
     *
     * @return bool
     */
    public function isSoapRequested()
    {
        //TODO: Implement isSoapRequested() method
    }

    /**
     * Checks whether request has been made using any secure layer
     * @return bool
     */
    public function isSecureRequest()
    {
        return ($this->getScheme() == 'https');
    }

    /**
     * Gets HTTP raw request body
     * @return string
     */
    public function getRawBody()
    {
        if ($this->body) {
            return $this->body;
        }

        $fh = fopen('php://input', 'r');
        $this->body = stream_get_contents($fh);
        fclose($fh);

        return $this->body;
    }

    /**
     * Gets decoded JSON HTTP raw request body
     * @return array|mixed
     */
    public function getJsonRawBody()
    {
        if ($this->jsonBody) {
            return $this->jsonBody;
        }

        if ($this->getContentType() == 'application/json') {
            return $this->jsonBody = json_decode($this->getRawBody());
        } else {
            return $this->jsonBody = '';
        }
    }

    /**
     * Gets active server address IP
     *
     * @return string
     */
    public function getServerAddress()
    {
        return $this->serverAddress;
    }

    /**
     * Gets active server name
     *
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * Gets information about schema, host and port used by the request
     *
     * @return string
     */
    public function getHttpHost()
    {
        return $this->httpHost;
    }

    /**
     * Gets most possible client IPv4 Address.
     * This method search in $_SERVER[‘REMOTE_ADDR’] and optionally in $_SERVER[‘HTTP_X_FORWARDED_FOR’]
     *
     * @return string
     */
    public function getClientAddress()
    {
        if ($this->trustProxy && getenv('HTTP_X_FORWARDED_FOR')) {
            return getenv('HTTP_X_FORWARDED_FOR');
        }

        return getenv('REMOTE_ADDR');
    }

    /**
     * Gets HTTP method which request has been made
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Gets HTTP URI which request has been made
     *
     * @return string
     */
    public function getURI()
    {
        return $this->uri;
    }

    /**
     * Gets HTTP user agent used to made the request
     *
     * @return string
     */
    public function getUserAgent()
    {
        return (getenv('HTTP_USER_AGENT') ? getenv('HTTP_USER_AGENT') : null);
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

        return ($this->getMethod() == $methods);
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
        return isset($this->superGlobals['files']);
    }

    /**
     * Gets attached files as Rad\Http\Request\File instance
     *
     * @return File[]
     */
    public function getUploadedFiles()
    {
        $output = [];

        foreach ($this->superGlobals['files'] as $fieldName => $file) {
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
     * Gets web page that refers active request. ie: http://www.google.com
     *
     * @return string|null
     */
    public function getHTTPReferer()
    {
        return getenv('HTTP_REFERER') ? getenv('HTTP_REFERER') : null;
    }

    /**
     * Gets array with mime/types and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT’]
     *
     * @return array
     */
    public function getAcceptableContent()
    {
        //TODO: Prepare result without parameters
        return getenv('HTTP_ACCEPT') ? explode(',', getenv('HTTP_ACCEPT')) : [];
    }

    /**
     * Gets best mime/type accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT’]
     *
     * @return array
     */
    public function getBestAccept()
    {
        //TODO: Implement getBestAccept() method
    }

    /**
     * Gets charsets array and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_CHARSET’]
     *
     * @return array
     */
    public function getClientCharsets()
    {
        //TODO: Prepare result without parameters
        return getenv('HTTP_ACCEPT_CHARSET') ? explode(',', getenv('HTTP_ACCEPT_CHARSET')) : [];
    }

    /**
     * Gets best charset accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_CHARSET’]
     *
     * @return string
     */
    public function getBestCharset()
    {
        //TODO: Implement getBestCharset() method
    }

    /**
     * Gets languages array and their quality accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_LANGUAGE’]
     *
     * @return array
     */
    public function getLanguages()
    {
        //TODO: Prepare result without parameters
        return getenv('HTTP_ACCEPT_LANGUAGE') ? explode(',', getenv('HTTP_ACCEPT_LANGUAGE')) : [];
    }

    /**
     * Gets best language accepted by the browser/client from $_SERVER[‘HTTP_ACCEPT_LANGUAGE’]
     *
     * @return string
     */
    public function getBestLanguage()
    {
        //TODO: Implement getBestLanguage() method
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
     * Prepare input
     *
     * @return mixed|string
     */
    protected function prepareInput()
    {
        $content = $this->getRawBody();

        if ($this->getContentType() == 'application/json') {
            $content = json_decode($content);
        } elseif ($this->getContentType() == 'application/x-www-form-urlencoded') {
            parse_str($content, $content);
        }

        return $content;
    }
}
