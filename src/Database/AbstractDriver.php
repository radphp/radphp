<?php

namespace Rad\Database;

use PDO;
use Rad\Database\Driver\DriverInterface;

/**
 * AbstractDriver
 *
 * @package Rad\Database
 */
abstract class AbstractDriver implements DriverInterface
{
    protected $config = [];
    /**
     * @var PDO
     */
    protected $connection;

    /**
     * Rad\Database\AbstractDriver constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return bool
     */
    abstract public function connect();

    /**
     * Disconnect connection
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
