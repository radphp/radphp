<?php

namespace Rad\Network\Http\Response;

/**
 * Http Response Headers
 *
 * @package Rad\Network\Http\Response
 */
class Headers implements HeadersInterface
{
    protected $headers = [];

    /**
     * Sets a header to be sent at the end of the request
     *
     * @param string $name  Http header name
     * @param string $value Http header value
     *
     * @return Headers
     */
    public function set($name, $value)
    {
        $this->headers[trim($name)] = trim($value);

        return $this;
    }

    /**
     * Gets a header value from the internal bag
     *
     * @param string $name Http header name
     *
     * @return string|null Return null on not exist header
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }

        return null;
    }

    /**
     * Sets a raw header to be sent at the end of the request
     *
     * @param string $header Http header
     *
     * @return Headers
     */
    public function setRaw($header)
    {
        $this->headers[trim($header)] = null;

        return $this;
    }

    /**
     * Removes a header to be sent at the end of the request
     *
     * @param string $headerIndex Http header name
     *
     * @return bool Return true on remove header otherwise return false
     */
    public function remove($headerIndex)
    {
        if (array_key_exists($headerIndex, $this->headers)) {
            unset($this->headers[$headerIndex]);

            return true;
        }

        return false;
    }

    /**
     * Sends the headers to the client
     */
    public function send()
    {
        foreach ($this->headers as $name => $value) {
            if (is_null($value)) {
                header($name);
            } else {
                header("{$name}: {$value}");
            }
        }
    }

    /**
     * Reset headers
     */
    public function reset()
    {
        $this->headers = [];
    }

    /**
     * Returns the current headers as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->headers;
    }
}
