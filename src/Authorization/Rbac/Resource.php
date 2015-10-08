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
     * @var string
     */
    protected $title = '';

    /**
     * @var string|null
     */
    protected $description = '';

    /**
     * Rad\Authorization\Rbac\Resource constructor
     *
     * @param string $name Resource name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Factory method for chain ability.
     *
     * @param string $name Resource name
     *
     * @return self
     */
    public static function create($name)
    {
        return new static($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Resource name must be string.');
        }

        $this->name = $name;

        return $this;
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
    public function setTitle($title)
    {
        $this->title = strval($title);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = strval($description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }
}
