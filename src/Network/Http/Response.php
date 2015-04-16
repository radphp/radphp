<?php

namespace Rad\Network\Http;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Rad\Network\Http\Response\CookiesInterface;
use Rad\Network\Http\Response\Exception;
use Rad\Network\Http\Response\Headers;
use Rad\Network\Http\Response\HeadersInterface;

/**
 * Http Response
 *
 * @package Rad\Network\Http
 */
class Response implements ResponseInterface
{
    /**
     * Protocol header to send to the client
     *
     * @var string
     */
    protected $protocol = 'HTTP/1.1';

    /**
     * Response content
     *
     * @var string
     */
    protected $content;

    /**
     * Buffer list of headers
     *
     * @var HeadersInterface
     */
    protected $headers;

    /**
     * Cookies bag
     *
     * @var CookiesInterface
     */
    protected $cookies;

    /**
     * Header is sent
     *
     * @var bool
     */
    protected $sent = false;

    /**
     * File path
     *
     * @var string
     */
    protected $file;

    /**
     * Holds HTTP response statuses
     *
     * @var array
     */
    protected $statusCodes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'Unsupported Version'
    ];

    /**
     * Response constructor
     *
     * @param string $content
     * @param int    $code
     * @param string $status
     */
    public function __construct($content = null, $code = 200, $status = null)
    {
        if (!is_null($content)) {
            $this->content = $content;
        }

        $this->setStatusCode($code, $status);
    }

    /**
     * Sets the HTTP response code
     *
     * @param int    $code    Response code
     * @param string $message Response message
     *
     * @return Response|ResponseInterface
     */
    public function setStatusCode($code, $message = null)
    {
        if (!array_key_exists($code, $this->statusCodes)) {
            throw new InvalidArgumentException('Status code is not valid.');
        }

        foreach ($this->getHeaders()->toArray() as $headerName => $headerValue) {
            if (strpos($headerName, $this->protocol) !== false) {
                $this->getHeaders()->remove($headerName);
                break;
            }
        }

        if (!$message || empty(trim($message))) {
            $message = $this->statusCodes[$code];
        }

        $this->getHeaders()->setRaw("{$this->protocol} {$code} {$message}");
        $this->getHeaders()->set('Status', "{$code} {$message}");

        return $this;
    }

    /**
     * Sets a headers bag for the response externally
     *
     * @param HeadersInterface $headers
     *
     * @return ResponseInterface
     */
    public function setHeaders(HeadersInterface $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Returns headers set by the user
     *
     * @return HeadersInterface
     */
    public function getHeaders()
    {
        if (!$this->headers) {
            return $this->headers = new Headers();
        }

        return $this->headers;
    }

    /**
     * Sets a cookies bag for the response externally
     *
     * @param CookiesInterface $cookies
     *
     * @return ResponseInterface
     */
    public function setCookies(CookiesInterface $cookies)
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * Returns cookies set by the user
     *
     * @return CookiesInterface
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Overwrites a header in the response
     *
     * @param string $name  Http header name
     * @param string $value Http header value
     *
     * @return ResponseInterface
     */
    public function setHeader($name, $value)
    {
        $this->getHeaders()->set($name, $value);

        return $this;
    }

    /**
     * Send a raw header to the response
     *
     * @param string $header Http header
     *
     * @return ResponseInterface
     */
    public function setRawHeader($header)
    {
        $this->getHeaders()->setRaw($header);

        return $this;
    }

    /**
     * Resets all the established headers
     *
     * @return ResponseInterface
     */
    public function resetHeaders()
    {
        $this->getHeaders()->reset();

        return $this;
    }

    /**
     * Sets a Expires header to use HTTP cache
     *
     * @param DateTime $datetime Expire time
     *
     * @return ResponseInterface
     */
    public function setExpires(DateTime $datetime)
    {
        $datetime->setTimezone(new DateTimeZone('UTC'));

        $this->getHeaders()->set('Expires', $datetime->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Sends a Not-Modified response
     *
     * @return ResponseInterface
     */
    public function setNotModified()
    {
        $this->setStatusCode(304);

        return $this;
    }

    /**
     * Sets the response content-type mime, optionally the charset
     *
     * @param string $contentType
     * @param string $charset
     *
     * @return ResponseInterface
     */
    public function setContentType($contentType, $charset = null)
    {
        if (is_null($charset)) {
            $this->getHeaders()->set('Content-Type', $contentType);
        } else {
            $this->getHeaders()->set('Content-Type', $contentType . '; charset=' . $charset);
        }

        return $this;
    }

    /**
     * Set a custom ETag
     *
     * @param string $hash The unique hash that identifies this response
     * @param bool   $weak Whether the response is semantically the same as other with the same hash or not
     *
     * @return Response
     */
    public function setEtag($hash, $weak = false)
    {
        $this->getHeaders()->set('Etag', sprintf('%s"%s"', ($weak) ? 'W/' : null, $hash));

        return $this;
    }

    /**
     * Redirect by HTTP to URL
     *
     * @param string $location   Location url
     * @param int    $statusCode Status code
     *
     * @return ResponseInterface
     */
    public function redirect($location, $statusCode = 302)
    {
        $this->setStatusCode($statusCode);
        $this->getHeaders()->set('Location', $location);

        return $this;
    }

    /**
     * Sets HTTP response body
     *
     * @param string $content Http response body
     *
     * @return ResponseInterface
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Sets HTTP response body. The parameter is automatically converted to JSON
     *
     * @param string|array $content    Http response body
     * @param int          $jsonOption bitmask consisting on http://www.php.net/manual/en/json.constants.php
     *
     * @return ResponseInterface
     */
    public function setJsonContent($content, $jsonOption = 0)
    {
        if (is_array($content)) {
            $this->content = json_encode($content, $jsonOption);
        } elseif (is_string($content)) {
            $this->content = $content;
        } else {
            throw new InvalidArgumentException('Input content type must be json string or array.');
        }

        $this->setContentType('application/json');

        return $this;
    }

    /**
     * Appends a string to the HTTP response body
     *
     * @param string $content Http response body
     *
     * @return ResponseInterface
     */
    public function appendContent($content)
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * Gets the HTTP response body
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Check if the response is already sent
     *
     * @return bool
     */
    public function isSent()
    {
        return $this->sent;
    }

    /**
     * Sends headers to the client
     *
     * @return ResponseInterface
     */
    public function sendHeaders()
    {
        if (is_object($this->headers)) {
            $this->headers->send();
        }

        return $this;
    }

    /**
     * Sends cookies to the client
     *
     * @return ResponseInterface
     */
    public function sendCookies()
    {
        if (is_object($this->cookies)) {
            $this->cookies->send();
        }

        return $this;
    }


    /**
     * Prints out HTTP response to the client
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function send()
    {
        if ($this->sent === false) {
            $this->sendHeaders();
            $this->sendCookies();

            if (!is_null($this->content)) {
                echo $this->getContent();
            } else {
                if (is_string($this->file)) {
                    if (!file_exists($this->file)) {
                        throw new Exception('The requested file was not found');
                    }
                    $handle = fopen($this->file, 'r');

                    if ($handle !== false) {
                        fpassthru($handle);
                        fclose($handle);
                    }
                }
            }

            $this->sent = true;

            return $this;
        }

        throw new Exception('Response was already sent');
    }

    /**
     * Sets an attached file to be sent at the end of the request
     *
     * @param string $filePath
     * @param string $attachmentName
     * @param bool   $attachment
     *
     * @return ResponseInterface
     */
    public function setFileToSend($filePath, $attachmentName = null, $attachment = false)
    {
        if (!is_string($attachmentName)) {
            $basePath = basename($filePath);
        } else {
            $basePath = $attachmentName;
        }

        if ($attachment) {
            $this->getHeaders()->setRaw('Content-Description: File Transfer');
            $this->getHeaders()->setRaw('Content-Disposition: attachment; filename="' . $basePath . '"');
            $this->getHeaders()->setRaw('Content-Transfer-Encoding: binary');
        }

        $this->file = $filePath;

        return $this;
    }
}
