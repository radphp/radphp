<?php

namespace Rad\Core;

use Rad\Core\Exception\BaseException;

/**
 * DotEnv loader
 *
 * @package Rad\Core
 */
class DotEnv
{
    /**
     * Load .env file
     *
     * @param string $path Directory path
     *
     * @throws BaseException
     */
    public static function load($path)
    {
        if (is_dir($path)) {
            foreach (preg_grep('/^\.env$/', scandir($path)) as $filename) {
                $filePath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
                if (is_readable($filePath)) {
                    foreach (parse_ini_file($filePath) as $envKey => $envValue) {
                        putenv($envKey . '=' . $envValue);
                    }
                }
            }
        } else {
            throw new BaseException('Your path does not directory.');
        }
    }
}
