<?php

namespace Rad\Authorization\Rbac;

use Countable;
use InvalidArgumentException;
use Iterator;

/**
 * Resource Collection
 *
 * @package Rad\Authorization\Rbac
 */
class ResourceCollection implements Iterator, Countable
{
    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $indexes = [];

    /**
     * Resource storage
     *
     * @var array
     */
    protected $resources = [];

    /**
     * ResourceCollection constructor.
     *
     * @param array $resources Resources
     */
    public function __construct(array $resources = [])
    {
        $this->setResources($resources);
    }

    /**
     * Adds a resource in the collection
     *
     * @param ResourceInterface $resource The Resource to add.
     *
     * @deprecated Please use add method
     * @return void
     */
    public function attach(ResourceInterface $resource)
    {
        trigger_error(
            'The attach() method is deprecated and will be removed in next version. Use add() instead.',
            E_USER_DEPRECATED
        );

        $this->add($resource);
    }

    /**
     * Adds a resource in the collection
     *
     * @param ResourceInterface $resource The Resource to add.
     *
     * @return self
     */
    public function add(ResourceInterface $resource)
    {
        $this->resources[$resource->getName()] = $resource;
        $this->indexes[] = $resource->getName();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResources(array $resources)
    {
        $this->removeAll();

        foreach ($resources as $resource) {
            if ($resource instanceof ResourceInterface) {
                $this->add($resource);
            } elseif (is_string($resource)) {
                $this->add(new Resource($resource));
            } else {
                throw new InvalidArgumentException(
                    'Resource must be string or an object implemented "Rad\Authorization\Rbac\ResourceInterface".'
                );
            }
        }

        return $this;
    }

    /**
     * Removes an resource from the collection
     *
     * @param ResourceInterface $resource The object to remove.
     *
     * @deprecated Please use remove method
     * @return void
     */
    public function detach(ResourceInterface $resource)
    {
        trigger_error(
            'The detach() method is deprecated and will be removed in next version. Use remove() instead.',
            E_USER_DEPRECATED
        );

        $this->remove($resource);
    }

    /**
     * Removes an resource from the collection
     *
     * @param ResourceInterface|string $resource The object or resource name to remove.
     *
     * @return bool If resource exists remove it and return true otherwise return false.
     */
    public function remove($resource)
    {
        $resourceName = $this->getResourceName($resource);
        $index = array_search($resourceName, $this->indexes, true);

        if (false !== $index) {
            unset($this->indexes[$index]);
            unset($this->resources[$resourceName]);

            return true;
        }

        return false;
    }

    /**
     * Remove all resources
     */
    public function removeAll()
    {
        $this->indexes = [];
        $this->resources = [];
        $this->position = 0;
    }

    /**
     * Checks if the collection contains a specific resource
     *
     * @param ResourceInterface|string $resource The resource to look for.
     *
     * @return bool true if the resource is in the storage, false otherwise.
     */
    public function contains($resource)
    {
        return array_key_exists($this->getResourceName($resource), $this->resources);
    }

    /**
     * Return the current resource
     *
     * @return ResourceInterface
     */
    public function current()
    {
        return $this->resources[$this->indexes[$this->position]];
    }

    /**
     * Move forward to next resource
     *
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current resource
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *        Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->indexes[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Count resources of an object
     *
     * @return int The custom count as an integer.
     *        The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->indexes);
    }

    /**
     * @param ResourceInterface|string $resource Resource name or object
     *
     * @return string
     */
    protected function getResourceName($resource)
    {
        if ($resource instanceof ResourceInterface) {
            $resource = $resource->getName();
        }

        if (false === is_string($resource)) {
            throw new InvalidArgumentException(
                'Resource argument must be string or an object implemented "Rad\Authorization\Rbac\ResourceInterface".'
            );
        }

        return $resource;
    }
}
