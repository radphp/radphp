<?php

namespace Rad\Network\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Http Message Response
 *
 * @package Rad\Network\Http\Message
 */
class Response implements MessageInterface, ResponseInterface
{
    use MessageTrait;

    /**
     * @var string
     */
    protected $reasonPhrase;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $codeReasonPhrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
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
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * Rad\Network\Http\Message\Response constructor
     *
     * @param string|resource|StreamInterface $body
     * @param int                             $status
     * @param string                          $reason
     * @param array                           $headers
     * @param string                          $version
     */
    public function __construct(
        $body = 'php://temp',
        $status = 200,
        $reason = '',
        array $headers = [],
        $version = '1.1'
    ) {
        if ($body instanceof StreamInterface) {
            $this->stream = $body;
        } else {
            $this->stream = new Stream($body);
        }

        $this->statusCode = intval($status);

        if (!$reason && isset($this->codeReasonPhrases[$status])) {
            $this->reasonPhrase = $this->codeReasonPhrases[$status];
        } else {
            $this->reasonPhrase = strval($reason);
        }

        $this->setHeaders($headers);
        $this->protocol = $version;
    }


    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $newInstance = clone $this;
        $newInstance->statusCode = intval($code);

        if (!$reasonPhrase && isset($this->codeReasonPhrases[$code])) {
            $newInstance->reasonPhrase = $this->codeReasonPhrases[$code];
        } else {
            $newInstance->reasonPhrase = strval($reasonPhrase);
        }

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }
}
