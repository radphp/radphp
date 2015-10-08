<?php

namespace Rad\Authorization\Rbac;

/**
 * Role Interface
 *
 * @package Rad\Authorization\Rbac
 */
interface RoleInterface
{
    /**
     * Set role name
     *
     * @param string $name Role name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Get role name
     *
     * @return string
     */
    public function getName();

    /**
     * Set role title
     *
     * @param string $title Role title
     *
     * @return self
     */
    public function setTitle($title);

    /**
     * Get role title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set role description
     *
     * @param string $description Role description
     *
     * @return self
     */
    public function setDescription($description);

    /**
     * Get role description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set resources
     *
     * @param ResourceCollection|array|null $resources Resources
     *
     * @return self
     */
    public function setResources($resources);

    /**
     * Get resources
     *
     * @return ResourceCollection
     */
    public function getResources();

    /**
     * Add resource
     *
     * @param ResourceInterface|string $resource Resource name or object
     *
     * @return self
     */
    public function addResource($resource);

    /**
     * Has resource
     *
     * @param ResourceInterface|string $resource Resource name or object
     *
     * @return bool
     */
    public function hasResource($resource);
}
