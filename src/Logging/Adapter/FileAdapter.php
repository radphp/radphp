<?php

namespace Rad\Logging\Adapter;

use Rad\Logging\AbstractAdapter;

/**
 * File Adapter
 *
 * @package Rad\Logging\Adapter
 */
class FileAdapter extends AbstractAdapter
{
    protected $handle;

    /**
     * Rad\Logging\Adapter\FileAdapter constructor
     *
     * @param string $filePath Log file path
     */
    public function __construct($filePath)
    {
        $dirPath = dirname($filePath);
        if (false === is_dir($dirPath)) {
            mkdir($dirPath, 0775, true);
        }

        if (substr($filePath, -4) !== '.log') {
            $filePath .= '.log';
        }

        $this->handle = fopen($filePath, 'ab+');
    }

    /**
     * Rad\Logging\Adapter\FileAdapter destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Closes an open file pointer
     *
     * @return bool
     */
    public function close()
    {
        return fclose($this->handle);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, $time, array $context = [])
    {
        fwrite($this->handle, $this->getFormatter()->format($level, $message, $time, $context));
    }
}
