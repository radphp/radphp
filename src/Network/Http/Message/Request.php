<?php

namespace Rad\Network\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Http Message Request
 *
 * @package Rad\Network\Http\Message
 */
class Request implements MessageInterface, RequestInterface
{
    use MessageTrait;

    /**
     * @var UriInterface
     */
    protected $uri;
    protected $method;
    protected $requestTarget;

    const METHOD_CONNECT = 'CONNECT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_PATCH = 'PATCH';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_TRACE = 'TRACE';

    /**
     * Rad\Network\Http\Message\Request constructor
     *
     * @param string|UriInterface             $uri
     * @param string                          $method
     * @param string|resource|StreamInterface $body
     * @param array                           $headers
     * @param string                          $protocol
     */
    public function __construct(
        $uri,
        $method,
        $body = null,
        array $headers = [],
        $protocol = '1.1'
    ) {
        if (is_string($uri)) {
            $this->uri = new Uri($uri);
        } elseif ($uri instanceof UriInterface) {
            $this->uri = $uri;
        } else {
            throw new InvalidArgumentException('Uri must be a string or instance of Psr\Http\Message\UriInterface');
        }

        $this->setHeaders($headers);

        if ($this->uri->getHost() && !$this->hasHeader('Host')) {
            $host = $this->uri->getHost();
            $host .= !is_null($this->uri->getPort()) ? ':' . $this->uri->getPort() : '';

            $this->headerNames['host'] = 'Host';
            $this->headerValues['host'] = [$host];
        }

        if ($body) {
            if ($body instanceof StreamInterface) {
                $this->stream = $body;
            } else {
                $this->stream = new Stream($body);
            }
        }

        $this->method = $method;
        $this->protocol = $protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        if (!$this->uri instanceof UriInterface) {
            return '/';
        }

        $this->requestTarget = $this->uri->getPath();
        if (!$this->requestTarget) {
            $this->requestTarget = '/';
        }

        if ($this->uri->getQuery()) {
            $this->requestTarget .= '?' . $this->uri->getQuery();
        }

        return $this->requestTarget;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Request target cannot contain whitespace'
            );
        }

        $newInstance = clone $this;
        $newInstance->requestTarget = $requestTarget;

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $newInstance = clone $this;
        $newInstance->method = strval($method);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $newInstance = clone $this;
        $newInstance->uri = $uri;

        if ($preserveHost) {
            return $newInstance;
        }

        if ($uri->getHost()) {
            $host = $uri->getHost();
            $host .= !is_null($uri->getPort()) ? ':' . $uri->getPort() : '';

            $newInstance->headerNames['host'] = 'Host';
            $newInstance->headerValues['host'] = $host;
        }

        return $newInstance;
    }
}
