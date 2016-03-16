<?php

namespace Rad\Network\Http\Client;

use Rad\Core\Exception\BaseException;
use Rad\Network\Http\ClientInterface;
use Rad\Network\Http\Message\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Http Curl Client
 *
 * @package Rad\Network\Http\Client
 */
class Curl implements ClientInterface
{
    protected $handle;
    protected $options = [
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOPROGRESS => true,
        CURLOPT_RETURNTRANSFER => true
    ];

    /**
     * Rad\Network\Http\Client\Curl constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->handle = curl_init();
        $this->options = $options + $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request)
    {
        $this->prepareOptions($request);

        $result = curl_exec($this->handle);

        if ($result === false) {
            throw new BaseException(curl_error($this->handle));
        }

        return $this->createResponse($result);
    }

    /**
     * Get info
     *
     * @param null|int $info
     *
     * @return mixed
     */
    public function getInfo($info = null)
    {
        return curl_getinfo($this->handle, $info);
    }

    /**
     * Curl destructor
     */
    public function __destruct()
    {
        curl_close($this->handle);
    }

    /**
     * Prepare options
     *
     * @param RequestInterface $request
     */
    protected function prepareOptions(RequestInterface $request)
    {
        $this->options[CURLOPT_URL] = (string)$request->getUri();
        $this->options[CURLOPT_HTTP_VERSION] = $request->getProtocolVersion();

        // Set request method
        if (strtoupper($request->getMethod()) === 'GET') {
            $this->options[CURLOPT_HTTPGET] = true;
        } elseif (strtoupper($request->getMethod()) === 'HEAD') {
            $this->options[CURLOPT_NOBODY] = true;
        } else {
            $this->options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
        }

        // Prepare and set request headers
        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $this->options[CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $name, $value);
            }
        }

        curl_setopt_array($this->handle, $this->options);
    }

    /**
     * Create response
     *
     * @param string $rawResponse
     *
     * @return Response
     */
    protected function createResponse($rawResponse)
    {
        $response = new Response();

        $headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);
        $rawHeaders = trim(substr($rawResponse, 0, $headerSize));
        $rawHeadersArray = explode("\r\n\r\n", $rawHeaders);

        if (!empty($rawHeadersArray)) {
            $headerLines = explode("\r\n", $rawHeadersArray[0]);
            foreach ($headerLines as $headerLine) {
                if (stripos(trim($headerLine), 'http', 0) === 0) {
                    if (preg_match('/^http\/((\d*[.])?\d+)\s+(\d+)\s+([a-z]+)/i', trim($headerLine), $matches)) {
                        $response = $response->withStatus($matches[3], $matches[4])
                            ->withProtocolVersion($matches[1]);
                    }
                    continue;
                }

                $header = explode(':', $headerLine, 2);
                $name = trim($header[0]);
                $value = trim($header[1]);

                if ($response->hasHeader($name)) {
                    $response = $response->withAddedHeader($name, $value);
                } else {
                    $response = $response->withHeader($name, $value);
                }
            }
        }

        $content = substr($rawResponse, $headerSize);
        $response->getBody()->write($content);
        $response->getBody()->rewind();

        return $response;
    }
}
