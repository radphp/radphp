<?php

namespace Rad\Network\Http\Message;

use Rad\Exception;
use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

/**
 * Http Message Stream
 *
 * @package Rad\Network\Http\Message
 */
class Stream implements StreamInterface
{
    protected $handle;
    protected $connectionErrors = [];

    protected $readModes = [
        'r',
        'w+',
        'r+',
        'x+',
        'c+',
        'rb',
        'w+b',
        'r+b',
        'x+b',
        'c+b',
        'rt',
        'w+t',
        'r+t',
        'x+t',
        'c+t',
        'a+'
    ];

    protected $writeModes = [
        'w',
        'w+',
        'rw',
        'r+',
        'x+',
        'c+',
        'wb',
        'w+b',
        'r+b',
        'x+b',
        'c+b',
        'w+t',
        'r+t',
        'x+t',
        'c+t',
        'a',
        'a+'
    ];

    /**
     * Rad\Network\Http\Message\Stream constructor
     *
     * @param string|resource $stream
     * @param string          $mode
     * @param bool            $useIncludePath
     * @param null|resource   $context
     *
     * @throws Exception
     */
    public function __construct($stream, $mode = 'r+', $useIncludePath = false, $context = null)
    {
        if (is_string($stream)) {
            set_error_handler([$this, 'connectionErrorHandler']);
            if ($context === null) {
                $this->handle = fopen($stream, $mode, $useIncludePath);
            } else {
                $this->handle = fopen($stream, $mode, $useIncludePath, $context);
            }
            restore_error_handler();

            if ($this->connectionErrors) {
                $lastError = end($this->connectionErrors);
                $exc = new Exception($lastError['message']);
                $exc->setFile($lastError['file'])
                    ->setLine($lastError['line']);

                throw $exc;
            }
        } elseif (is_resource($stream)) {
            $this->handle = $stream;
        } else {
            throw new InvalidArgumentException('Stream argument type must be resource or string.');
        }
    }

    /**
     * Connection error handler
     *
     * @param int    $code    Error code
     * @param string $message Error message
     * @param string $file    Error file
     * @param int    $line    Error line
     * @param array  $args    Error arguments
     */
    protected function connectionErrorHandler($code, $message, $file, $line, $args)
    {
        $this->connectionErrors[] = [
            'code' => $code,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'args' => $args,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            $this->seek(0);

            return (string)stream_get_contents($this->handle);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        fclose($this->handle);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if (!is_resource($this->handle)) {
            return null;
        }

        $handle = $this->handle;
        $this->handle = null;

        return $handle;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (!is_resource($this->handle)) {
            return null;
        }

        $stat = fstat($this->handle);

        if (isset($stat['size'])) {
            return $stat['size'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('Can not call tell, resource not available.');
        }

        $position = ftell($this->handle);

        if (!is_int($position)) {
            throw new RuntimeException('Occurred error, position of the file pointer is not integer.');
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        if (!is_resource($this->handle)) {
            return true;
        }

        return feof($this->handle);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        if (!is_resource($this->handle)) {
            return false;
        }

        return stream_get_meta_data($this->handle)['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('Resource is not available.');
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable.');
        }

        $output = fseek($this->handle, $offset, $whence);

        if ($output !== 0) {
            throw new RuntimeException('Occurred error, seeks on a file pointer');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        if (!is_resource($this->handle)) {
            return false;
        }

        return in_array(stream_get_meta_data($this->handle)['mode'], $this->writeModes);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('Resource is not available.');
        }

        $output = fwrite($this->handle, $string);

        if ($output === false) {
            throw new RuntimeException('Occurred error on write data to the stream.');
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        if (!is_resource($this->handle)) {
            return false;
        }

        return in_array(stream_get_meta_data($this->handle)['mode'], $this->readModes);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('Resource is not available.');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable.');
        }

        $output = fread($this->handle, $length);

        if ($output === false) {
            throw new RuntimeException('Occurred error on read data from the stream.');
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (!is_resource($this->handle)) {
            throw new RuntimeException('Resource is not available.');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable.');
        }

        $output = stream_get_contents($this->handle);

        if ($output === false) {
            throw new RuntimeException('Occurred error on get contents.');
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metaData = stream_get_meta_data($this->handle);

        if (null === $key) {
            return $metaData;
        } elseif (array_key_exists($key, $metaData)) {
            return $metaData[$key];
        }

        return null;
    }
}
