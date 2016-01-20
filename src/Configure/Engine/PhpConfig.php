<?php

namespace Rad\Configure\Engine;

use Rad\Configure\EngineInterface;
use Rad\Configure\Exception;
use SplFileInfo;

/**
 * PhpConfig Engine
 *
 * @package Rad\Configure\Engine
 */
class PhpConfig implements EngineInterface
{
    /**
     * Load config
     *
     * @param string|array $config Config file or php array
     *
     * @return array|mixed|string
     * @throws Exception
     */
    public function load($config)
    {
        if (is_string($config)) {
            if (!file_exists($config) || !is_readable($config)) {
                throw new Exception(sprintf('Input file "%s" is not exist or is not readable', $config));
            }

            $output = include $config;

            if (!is_array($output)) {
                throw new Exception('You must return array in config file');
            }

            return $output;
        } elseif (is_array($config)) {
            return $config;
        } else {
            throw new Exception('Input data is not valid');
        }
    }

    /**
     * Dump config to file
     *
     * @param string $file File path for dump config into
     * @param array  $data Config data
     *
     * @return bool
     * @throws Exception
     */
    public function dump($file, array $data)
    {
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileInfo = new SplFileInfo($file);

        if ($fileInfo->isFile()) {
            if ($fileInfo->isWritable()) {
                $fileObject = $fileInfo->openFile('w');
            } else {
                throw new Exception(sprintf('File "%s" is not writable.', $file));
            }
        } else {
            $fileObject = $fileInfo->openFile('w');
        }

        if (null !== $fileObject->fwrite('<?php return ' . var_export($data, true) . ';')) {
            return true;
        }

        return false;
    }
}
