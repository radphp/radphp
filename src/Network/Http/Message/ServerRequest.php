<?php

namespace Rad\Network\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Http Message ServerRequest
 *
 * @package Rad\Network\Http\Message
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    protected $serverParams = [];
    protected $cookieParams = [];
    protected $queryParams = [];
    protected $uploadedFiles = [];
    protected $parsedBody;
    protected $attributes = [];

    /**
     * Rad\Network\Http\Message\ServerRequest constructor
     *
     * @param array                           $serverParams
     * @param array                           $uploadedFiles
     * @param UriInterface|string             $uri
     * @param array                           $method
     * @param StreamInterface|string|resource $body
     * @param array                           $headers
     * @param string                          $protocol
     */
    public function __construct(
        array $serverParams,
        array $uploadedFiles,
        $uri,
        $method,
        $body = 'php://input',
        array $headers = [],
        $protocol = '1.1'
    ) {
        parent::__construct($uri, $method, $body, $headers, $protocol);
        $this->validateUploadedFiles($uploadedFiles);

        $this->serverParams = $serverParams;
        $this->uploadedFiles = $uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $newInstance = clone $this;
        $newInstance->cookieParams = $cookies;

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $newInstance = clone $this;
        $newInstance->queryParams = $query;

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->validateUploadedFiles($uploadedFiles);

        $newInstance = clone $this;
        $newInstance->uploadedFiles = $uploadedFiles;

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        if (!is_null($data) || !is_array($data) || !is_object($data)) {
            throw new InvalidArgumentException('Invalid parsed body data.');
        }

        $newInstance = clone $this;
        $newInstance->parsedBody = $data;

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->attributes[$name] = $value;

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $newInstance = clone $this;
        if (array_key_exists($name, $this->attributes)) {
            unset($newInstance->attributes[$name]);
        }

        return $newInstance;
    }

    /**
     * Validate uploaded files
     *
     * @param array $uploadedFiles
     */
    protected function validateUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
                continue;
            }

            if (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid uploaded file.');
            }
        }
    }
}
