<?php

namespace Rad\Authorization\Rbac;

use SplObjectStorage;

/**
 * Resource Collection
 *
 * @package Rad\Authorization\Rbac
 */
class ResourceCollection extends SplObjectStorage
{
    /**
     * Adds a resource in the collection
     *
     * @param ResourceInterface $resource The Resource to add.
     *
     * @return void
     */
    public function attach(ResourceInterface $resource)
    {
        parent::attach($resource, $resource->getName());
    }

    /**
     * Removes an resource from the collection
     *
     * @param ResourceInterface $resource The object to remove.
     *
     * @return void
     */
    public function detach(ResourceInterface $resource)
    {
        parent::detach($resource);
    }

    /**
     * Checks if the collection contains a specific resource
     *
     * @param ResourceInterface $resource The resource to look for.
     *
     * @return bool true if the resource is in the storage, false otherwise.
     */
    public function contains(ResourceInterface $resource)
    {
        return parent::contains($resource);
    }

    /**
     * Checks whether an resource exists in the collection
     *
     * @param ResourceInterface $resource The resource to look for.
     *
     * @return bool true if the object exists in the storage,
     * and false otherwise.
     */
    public function offsetExists($resource)
    {
        return $this->contains($resource);
    }

    /**
     * Associates data to an resource in the collection
     *
     * @param ResourceInterface $resource The object to associate data with.
     * @param string            $data     The data to associate with the object.
     *
     * @return void
     */
    public function offsetSet($resource, $data = null)
    {
        $this->attach($resource);
    }

    /**
     * Removes an resource from the storage
     *
     * @param ResourceInterface $resource The object to remove.
     *
     * @return void
     */
    public function offsetUnset($resource)
    {
        $this->detach($resource);
    }

    /**
     * Returns the data associated with a resource
     *
     * @param ResourceInterface $resource The resource to look for.
     *
     * @return string The data previously associated with the resource in the collection.
     */
    public function offsetGet($resource)
    {
        return $resource->getName();
    }

    /**
     * Calculate a unique identifier for the contained objects
     *
     * @param ResourceInterface $resource resource whose identifier is to be calculated.
     *
     * @return string A string with the calculated identifier.
     */
    public function getHash(ResourceInterface $resource)
    {
        return $resource->getName();
    }
}
