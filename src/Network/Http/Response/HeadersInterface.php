<?php

namespace Rad\Network\Http\Response;

/**
 * Headers Interface
 *
 * @package Rad\Network\Http\Response
 */
interface HeadersInterface
{
    /**
     * Sets a header to be sent at the end of the request
     *
     * @param string $name  Http header name
     * @param string $value Http header value
     *
     * @return Headers
     */
    public function set($name, $value);

    /**
     * Gets a header value from the internal bag
     *
     * @param string $name Http header name
     *
     * @return string|null Return null on not exist header
     */
    public function get($name);

    /**
     * Sets a raw header to be sent at the end of the request
     *
     * @param string $header Http header
     *
     * @return Headers
     */
    public function setRaw($header);

    /**
     * Sends the headers to the client
     */
    public function send();

    /**
     * Reset headers
     */
    public function reset();

    /**
     * Returns the current headers as an array
     *
     * @return array
     */
    public function toArray();
}
