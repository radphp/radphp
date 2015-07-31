<?php

namespace Rad\Authorization\Rbac;

use InvalidArgumentException;

/**
 * Resource
 *
 * @package Rad\Authorization\Rbac
 */
class Resource implements ResourceInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * Rad\Authorization\Rbac\Resource constructor
     *
     * @param string      $name        Resource name
     * @param string|null $description Resource description
     */
    public function __construct($name, $description = null)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Resource name must be string.');
        }

        if (!is_string($description) && !is_null($description)) {
            throw new InvalidArgumentException('Resource description must be string or null.');
        }

        $this->name = $name;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }
}
