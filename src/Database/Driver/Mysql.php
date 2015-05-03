<?php

namespace Rad\Database\Driver;

use PDO;
use Rad\Database\AbstractDriver;

/**
 * Mysql driver
 *
 * @package Rad\Database\Driver
 */
class Mysql extends AbstractDriver
{
    protected $defaultConfig = [
        'port' => 3306
    ];

    const DSN_PREFIX = 'mysql';

    /**
     * Connect to database
     *
     * @return bool
     */
    public function connect()
    {
        if ($this->connection) {
            return true;
        }

        $config = $this->config + $this->defaultConfig;

        $dsn = sprintf(
            '%s:host=%s;port=%p;dbname=%s',
            self::DSN_PREFIX,
            $config['host'],
            $config['port'],
            $config['database']
        );

        if ($config['unix_socket']) {
            $dsn = sprintf(
                '%s:unix_socket=%s;dbname=%s',
                self::DSN_PREFIX,
                $config['unix_socket'],
                $config['database']
            );
        }

        $this->connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options']
        );

        return true;
    }
}
