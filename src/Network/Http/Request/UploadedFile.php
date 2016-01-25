<?php

namespace Rad\Network\Http\Request;

use finfo;
use Rad\Network\Http\Message\UploadedFile as BaseUploadedFile;

/**
 * Class File
 *
 * @package Rad\Network\Http\Request
 */
class UploadedFile extends BaseUploadedFile
{
    /**
     * Gets the real mime type of the upload file using finfo
     *
     * @return string
     */
    public function getRealType()
    {
        $fInfo = new finfo(FILEINFO_MIME);

        return $fInfo->file($this->file);
    }

    /**
     * Gets the file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->clientFilename, PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }


}
