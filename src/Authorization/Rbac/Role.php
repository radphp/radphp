<?php

namespace Rad\Authorization\Rbac;

use InvalidArgumentException;

/**
 * Role
 *
 * @package Rad\Authorization\Rbac
 */
class Role implements RoleInterface
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
     * @var string
     */
    protected $description = '';

    /**
     * @var ResourceCollection
     */
    protected $resourceCollection;

    /**
     * Rad\Authorization\Rbac\Role constructor
     *
     * @param string                   $name      Role name
     * @param ResourceCollection|array $resources Resources
     */
    public function __construct($name, $resources = [])
    {
        $this->setResources($resources)
            ->setName($name);
    }

    /**
     * Factory method for chain ability.
     *
     * @param string                   $name      Role name
     * @param ResourceCollection|array $resources Resources
     *
     * @return self
     */
    public static function create($name, $resources = [])
    {
        return new static($name, $resources);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Role name must be string.');
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

    /**
     * {@inheritdoc}
     */
    public function setResources($resources)
    {
        if ($resources instanceof ResourceCollection) {
            $this->resourceCollection = $resources;
        } else {
            $this->getResources()->setResources($resources);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        if (!$this->resourceCollection) {
            $this->resourceCollection = new ResourceCollection();
        }

        return $this->resourceCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function addResource($resource)
    {
        if (!$resource instanceof ResourceInterface) {
            $resource = new Resource($resource);
        }

        $this->getResources()->add($resource);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasResource($resource)
    {
        return $this->getResources()->contains($resource);
    }
}
