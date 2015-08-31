<?php

namespace Rad\Network\Http\Message;

use RuntimeException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Http Message UploadedFile
 *
 * @package Rad\Network\Http\Message
 */
class UploadedFile implements UploadedFileInterface
{
    protected $file;
    protected $clientFilename = null;
    protected $clientMediaType = null;
    protected $size = null;
    protected $error;
    protected $stream;
    protected $moved = false;
    private $validErrorCode = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION
    ];

    /**
     * Rad\Network\Http\Message\UploadedFile constructor
     *
     * @param string      $file            File path
     * @param int         $error           Upload error code
     * @param string|null $clientFilename  Filename
     * @param string|null $clientMediaType File type
     * @param int|null    $size            File size
     */
    public function __construct($file, $error, $clientFilename = null, $clientMediaType = null, $size = null)
    {
        $this->file = $file;

        if (!is_null($clientFilename) && !is_string($clientFilename)) {
            throw new InvalidArgumentException('Invalid client filename.');
        }
        $this->clientFilename = $clientFilename;

        if (!is_null($clientMediaType) && !is_string($clientMediaType)) {
            throw new InvalidArgumentException('Invalid client media type.');
        }
        $this->clientMediaType = $clientMediaType;

        if (!is_null($size) && !is_int($size)) {
            throw new InvalidArgumentException('Invalid file size.');
        }
        $this->size = $size;

        if (!in_array($error, $this->validErrorCode)) {
            throw new InvalidArgumentException('Error code not valid.');
        }
        $this->error = intval($error);
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        if ($this->moved === true) {
            throw new RuntimeException('Cannot get stream of moved file');
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error occurred on file upload');
        }

        return $this->stream = new Stream($this->file);
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath)
    {
        if ($this->moved === true) {
            throw new RuntimeException('File moved already');
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error occurred on file upload');
        }

        if (!is_writable($targetPath)) {
            throw new InvalidArgumentException('Target path is not writable');
        }

        if (!is_file($this->file)) {
            throw new RuntimeException('Uploaded file doesn\'t exist');
        }

        if (empty(PHP_SAPI) || 'cli' === PHP_SAPI) {
            //non-SAPI environments
            $targetPath = rtrim($targetPath, DIRECTORY_SEPARATOR);
            if (false === rename($this->file, $targetPath . DIRECTORY_SEPARATOR . $this->clientFilename)) {
                throw new RuntimeException('Cannot move file.');
            };
        } else {
            //SAPI environments
            if (false === move_uploaded_file($this->file, $targetPath . DIRECTORY_SEPARATOR . $this->clientFilename)) {
                throw new RuntimeException('Cannot move file');
            }
        }

        $this->moved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * {$@inheritdoc}
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * {$@inheritdoc}
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}
