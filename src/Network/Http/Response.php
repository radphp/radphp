<?php

namespace Rad\Network\Http;

use DateTime;
use DateTimeZone;
use Rad\Network\Http\Message\Stream;
use Rad\Network\Http\Response\CookiesInterface;
use Rad\Network\Http\Response\Exception;
use Rad\Network\Http\Message\Response as MessageResponse;

/**
 * Http Response
 *
 * @package Rad\Network\Http
 */
class Response extends MessageResponse
{
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
     * Rad\Network\Http\Response constructor
     *
     * @param string $content
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     */
    public function __construct($content = '', $status = 200, $reason = '', array $headers = [])
    {
        $body = new Stream('php://temp');
        $body->write($content);

        parent::__construct($body, $status, $reason, $headers);
    }

    /**
     * Factory method for chain ability.
     *
     * @param string $content
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     * @param string $version
     *
     * @return Response
     */
    public static function create($content = '', $status = 200, $reason = '', array $headers = [], $version = '1.1')
    {
        return new static($content, $status, $reason, $headers, $version);
    }

    /**
     * Sets a cookies bag for the response externally
     *
     * @param CookiesInterface $cookies
     *
     * @return Response
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
     * Sets a Expires header to use HTTP cache
     *
     * @param DateTime $datetime Expire time
     *
     * @return Response
     */
    public function withExpires(DateTime $datetime)
    {
        $datetime->setTimezone(new DateTimeZone('UTC'));

        return $this->withHeader('Expires', $datetime->format('D, d M Y H:i:s') . ' GMT');
    }

    /**
     * Get a Expires header to use HTTP cache
     *
     * @return string
     */
    public function getExpires()
    {
        return $this->getHeaderLine('Expires');
    }

    /**
     * Sends a Not-Modified response
     *
     * @return Response
     */
    public function withNotModified()
    {
        return $this->withStatus(304)
            ->withoutHeader('Allow')
            ->withoutHeader('Content-Encoding')
            ->withoutHeader('Content-Language')
            ->withoutHeader('Content-Length')
            ->withoutHeader('Content-MD5')
            ->withoutHeader('Content-Type')
            ->withoutHeader('Last-Modified')
            ->withBody(new Stream('php://temp'));
    }

    /**
     * Sets the response content-type mime, optionally the charset
     *
     * @param string $contentType
     * @param string $charset
     *
     * @return Response
     */
    public function withContentType($contentType, $charset = null)
    {
        if (is_null($charset)) {
            return $this->withHeader('Content-Type', $contentType);
        }

        return $this->withHeader('Content-Type', $contentType . '; charset=' . $charset);
    }

    /**
     * Set a custom ETag
     *
     * @param string $hash The unique hash that identifies this response
     * @param bool   $weak Whether the response is semantically the same as other with the same hash or not
     *
     * @return Response
     */
    public function withEtag($hash, $weak = false)
    {
        return $this->withHeader('Etag', sprintf('%s"%s"', ($weak) ? 'W/' : null, $hash));
    }

    /**
     * Gets the HTTP response body
     *
     * @return string
     */
    public function getContent()
    {
        return (string)$this->getBody();
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
     * @return Response
     */
    public function sendHeaders()
    {
        foreach ($this->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        return $this;
    }

    /**
     * Sends cookies to the client
     *
     * @return Response
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
     * @return Response
     * @throws Exception
     */
    public function send()
    {
        if ($this->sent === false) {
            $this->sendHeaders();
            $this->sendCookies();

            $content = (string)$this->getBody();
            if (!empty($content)) {
                echo $content;
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
     * @return Response
     */
    public function withFileToSend($filePath, $attachmentName = null, $attachment = false)
    {
        if (!is_string($attachmentName)) {
            $basePath = basename($filePath);
        } else {
            $basePath = $attachmentName;
        }

        $this->file = $filePath;

        if ($attachment) {
            return $this->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $basePath . '"')
                ->withHeader('Content-Transfer-Encoding', 'binary');
        }

        return $this;
    }
}
