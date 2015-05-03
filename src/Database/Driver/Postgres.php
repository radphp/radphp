<?php

namespace Rad\Database\Driver;

use PDO;
use Rad\Database\AbstractDriver;

/**
 * Postgres driver
 *
 * @package Rad\Database\Driver
 */
class Postgres extends AbstractDriver
{
    protected $defaultConfig = [
        'port' => 5432
    ];

    const DSN_PREFIX = 'pgsql';

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
            '%s:host=%s;port=%s;dbname=%s',
            self::DSN_PREFIX,
            $config['host'],
            $config['port'],
            $config['database']
        );

        $this->connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options']
        );

        return true;
    }
}
