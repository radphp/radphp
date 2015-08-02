<?php

namespace Rad\Authentication\Repository;

use PDO;
use Rad\Database\Connection;
use Rad\Authentication\Exception;
use Rad\Authentication\AbstractRepository;
use Rad\Authentication\Exception\IdentityNotFoundException;
use Rad\Authentication\Exception\CredentialInvalidException;

/**
 * Database Repository
 *
 * @package Rad\Authentication\Repository
 */
class DatabaseRepository extends AbstractRepository
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tableName = 'users';

    /**
     * @var string
     */
    protected $identityColumn = 'username';

    /**
     * @var string
     */
    protected $credentialColumn = 'password';

    /**
     * @var string
     */
    protected $conditions = '';

    /**
     * Rad\Authentication\Repository\DatabaseRepository constructor
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    /**
     * Find user
     *
     * @param string $identity
     * @param string $credential
     *
     * @return array|bool Return false when user not false, return array on authenticated
     * @throws Exception
     * @throws CredentialInvalidException
     * @throws IdentityNotFoundException
     */
    public function findUser($identity, $credential)
    {
        $this->connection->connect();
        $stmt = $this->connection->prepare(
            "SELECT * FROM \"{$this->tableName}\" WHERE \"{$this->identityColumn}\" = :username " . $this->conditions
        );

        $stmt->execute([':username' => $identity]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result !== false || $stmt->rowCount() === 0) {
            if (!empty($result)) {
                if (isset($result[$this->credentialColumn])) {
                    if ($this->passwordCrypt->verify($credential, $result[$this->credentialColumn]) === true) {
                        return $result;
                    }

                    throw new CredentialInvalidException;
                }

                throw new Exception(sprintf('Column "%s" does not exists.', $this->credentialColumn));
            }

            throw new IdentityNotFoundException;
        }

        throw new Exception($stmt->errorInfo()[2], $stmt->errorInfo()[1]);
    }

    /**
     * Set table name
     *
     * @param string $tableName Users table name
     *
     * @return self
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Set identity column
     *
     * @param string $identityColumn Identity column
     *
     * @return self
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->identityColumn = $identityColumn;

        return $this;
    }

    /**
     * Set credential column
     *
     * @param string $credentialColumn Credential column
     *
     * @return self
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->credentialColumn = $credentialColumn;

        return $this;
    }

    /**
     * Set conditions
     *
     * @param string $conditions Query condition
     *
     * @return self
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }
}
