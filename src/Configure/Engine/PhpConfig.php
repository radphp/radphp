<?php

namespace Rad\Configure\Engine;

use Rad\Configure\EngineInterface;
use Rad\Configure\Exception;

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
     */
    public function dump($file, array $data)
    {
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        if (false !== file_put_contents($file, '<?php return ' . var_export($data, true) . ';')) {
            return true;
        }

        return false;
    }
}
