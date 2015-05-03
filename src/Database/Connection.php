<?php

namespace Rad\Database;

/**
 * Database Connection
 *
 * @package Rad\Database
 */
class Connection
{
    /**
     * Connection name
     *
     * @var string
     */
    protected $name;

    /**
     * Connection driver
     *
     * @var AbstractDriver
     */
    protected $driver;

    /**
     * Rad\Database\Connection constructor
     *
     * @param string $name
     * @param string $driver
     * @param array  $config
     */
    public function __construct($name, $driver, array $config)
    {
        $config = $config + [
                'host' => 'localhost',
                'database' => 'rad',
                'username' => 'root',
                'password' => '',
                'options' => []
            ];

        $this->name = $name;
        $this->driver = DriverFactory::get($driver, $config);
    }

    /**
     * Connect to database
     *
     * @return bool
     */
    public function connect()
    {
        return $this->driver->connect();
    }

    /**
     * Disconnect connection
     */
    public function disconnect()
    {
        $this->driver->disconnect();
    }

    /**
     * @param $statement
     *
     * @return int
     */
    public function execute($statement)
    {
        return $this->driver->getConnection()->exec($statement);
    }

    /**
     * @param null $name
     *
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->driver->getConnection()->lastInsertId($name);
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->driver->getConnection()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->driver->getConnection()->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->driver->getConnection()->rollBack();
    }

    /**
     * @param       $statement
     * @param array $driverOptions
     *
     * @return \PDOStatement
     */
    public function prepare($statement, array $driverOptions = [])
    {
        return $this->driver->getConnection()->prepare($statement, $driverOptions);
    }

    /**
     * @param $statement
     *
     * @return \PDOStatement
     */
    public function query($statement)
    {
        return $this->driver->getConnection()->query($statement);
    }

    /**
     * @return mixed
     */
    public function errorCode()
    {
        return $this->driver->getConnection()->errorCode();
    }

    /**
     * @return array
     */
    public function errorInfo()
    {
        return $this->driver->getConnection()->errorInfo();
    }
}
