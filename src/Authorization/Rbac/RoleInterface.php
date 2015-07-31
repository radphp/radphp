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
     * Get role name
     *
     * @return string
     */
    public function getName();

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
