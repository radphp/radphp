<?php

namespace Rad\Network\Http\Request;

use finfo;
use SplFileInfo;

/**
 * Class File
 *
 * @package Rad\Network\Http\Request
 */
class File extends SplFileInfo implements FileInterface
{
    private $key;
    private $file;

    /**
     * Construct a new uploaded file
     *
     * @param array $file Uploaded file
     */
    public function __construct($key, array $file)
    {
        parent::__construct($file['tmp_name']);

        $this->key = $key;
        $this->file = $file;
    }

    /**
     * Returns the real name of the uploaded file
     *
     * @return string
     */
    public function getName()
    {
        return $this->file['name'];
    }

    /**
     * Returns the temporal name of the uploaded file
     *
     * @return string
     */
    public function getTempName()
    {
        return $this->file['tmp_name'];
    }

    /**
     * Returns the mime type reported by the browser
     * This mime type is not completely secure, use getRealType() instead
     *
     * @return string
     */
    public function getType()
    {
        return $this->file['type'];
    }

    /**
     * Gets the real mime type of the upload file using finfo
     *
     * @return string
     */
    public function getRealType()
    {
        $fInfo = new finfo(FILEINFO_MIME);

        return $fInfo->file($this->file['tmp_name']);
    }

    /**
     * Gets the error code
     *
     * @return int
     */
    public function getError()
    {
        return $this->file['error'];
    }

    /**
     * Gets the file key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Checks whether the file has been uploaded via Post.
     *
     * @return bool
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->file['tmp_name']);
    }

    /**
     * Move the temporary file to a destination
     *
     * @param string $destination File destination
     *
     * @return boolean
     */
    public function moveTo($destination)
    {
        return move_uploaded_file($this->file['tmp_name'], $destination);
    }

    /**
     * Gets the file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->file['name'], PATHINFO_EXTENSION);
    }
}
