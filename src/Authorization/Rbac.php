<?php

namespace Rad\Authorization;

use InvalidArgumentException;
use Rad\Authorization\Rbac\Role;
use Rad\Authorization\Rbac\RoleInterface;
use Rad\Authorization\Rbac\ResourceInterface;
use Rad\Authorization\Rbac\ResourceCollection;

/**
 * Rbac
 *
 * Role Based Access Control
 *
 * @package Rad\Authorization
 */
class Rbac
{
    /**
     * @var RoleInterface[]
     */
    protected $roles = [];

    /**
     * Add role override if exists
     *
     * @param RoleInterface|string     $role      Role name or object
     * @param ResourceCollection|array $resources Role resources
     */
    public function addRole($role, array $resources = [])
    {
        if ($role instanceof RoleInterface) {
            $this->roles[$role->getName()] = $role;
        } else {
            $role = new Role($role, $resources);
            $this->roles[$role->getName()] = $role;
        }
    }

    /**
     * Get role
     *
     * @param string $name The role name
     *
     * @return null|RoleInterface
     */
    public function getRole($name)
    {
        if (isset($this->roles[$name])) {
            return $this->roles[$name];
        }

        return null;
    }

    /**
     * Has role exists
     *
     * @param RoleInterface|string $role Role name or object
     *
     * @return bool
     */
    public function hasRole($role)
    {
        if ($role instanceof RoleInterface) {
            $role = $role->getName();
        }

        if (!is_string($role)) {
            throw new InvalidArgumentException(
                'Role argument must be string or an object implemented "Rad\Authorization\Rbac\RoleInterface".'
            );
        }

        return isset($this->roles[$role]);
    }

    /**
     * User is granted
     *
     * @param ResourceInterface|string $resource Resource name or object
     *
     * @return bool
     */
    public function isGranted($resource)
    {
        foreach ($this->roles as $role) {
            if (true === $role->hasResource($resource)) {
                return true;
            }
        }

        return false;
    }
}
