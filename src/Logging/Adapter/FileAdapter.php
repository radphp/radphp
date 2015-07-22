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
