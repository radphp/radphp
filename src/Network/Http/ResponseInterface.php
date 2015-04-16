<?php

namespace Rad\Network\Http;

use DateTime;
use Rad\Network\Http\Response\Exception;
use Rad\Network\Http\Response\HeadersInterface;

/**
 * Response Interface
 *
 * @package Rad\Network\Http
 */
interface ResponseInterface
{
    /**
     * Sets the HTTP response code
     *
     * @param int    $code    Response code
     * @param string $message Response message
     *
     * @return ResponseInterface
     */
    public function setStatusCode($code, $message = null);

    /**
     * Sets a headers bag for the response externally
     *
     * @param HeadersInterface $headers
     *
     * @return ResponseInterface
     */
    public function setHeaders(HeadersInterface $headers);

    /**
     * Returns headers set by the user
     *
     * @return HeadersInterface
     */
    public function getHeaders();

    /**
     * Overwrites a header in the response
     *
     * @param string $name  Http header name
     * @param string $value Http header value
     *
     * @return ResponseInterface
     */
    public function setHeader($name, $value);

    /**
     * Send a raw header to the response
     *
     * @param string $header Http header
     *
     * @return ResponseInterface
     */
    public function setRawHeader($header);

    /**
     * Resets all the stablished headers
     *
     * @return ResponseInterface
     */
    public function resetHeaders();

    /**
     * Sets a Expires header to use HTTP cache
     *
     * @param DateTime $datetime Expire time
     *
     * @return ResponseInterface
     */
    public function setExpires(DateTime $datetime);

    /**
     * Sends a Not-Modified response
     *
     * @return ResponseInterface
     */
    public function setNotModified();

    /**
     * Sets the response content-type mime, optionally the charset
     *
     * @param string $contentType
     * @param string $charset
     *
     * @return ResponseInterface
     */
    public function setContentType($contentType, $charset = null);

    /**
     * Redirect by HTTP to URL
     *
     * @param string $location   Location url
     * @param int    $statusCode Status code
     *
     * @return ResponseInterface
     */
    public function redirect($location, $statusCode = 302);

    /**
     * Sets HTTP response body
     *
     * @param string $content Http response body
     *
     * @return ResponseInterface
     */
    public function setContent($content);

    /**
     * Sets HTTP response body. The parameter is automatically converted to JSON
     *
     * @param string|array $content    Http response body
     * @param int          $jsonOption bitmask consisting on http://www.php.net/manual/en/json.constants.php
     *
     * @return ResponseInterface
     */
    public function setJsonContent($content, $jsonOption = 0);

    /**
     * Appends a string to the HTTP response body
     *
     * @param string $content Http response body
     *
     * @return ResponseInterface
     */
    public function appendContent($content);

    /**
     * Gets the HTTP response body
     *
     * @return string
     */
    public function getContent();

    /**
     * Sends headers to the client
     *
     * @return ResponseInterface
     */
    public function sendHeaders();

    /**
     * Sends cookies to the client
     *
     * @return ResponseInterface
     */
    public function sendCookies();

    /**
     * Prints out HTTP response to the client
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function send();

    /**
     * Sets an attached file to be sent at the end of the request
     *
     * @param string $filePath
     * @param string $attachmentName
     * @param bool   $attachment
     *
     * @return ResponseInterface
     */
    public function setFileToSend($filePath, $attachmentName = null, $attachment = false);
}
