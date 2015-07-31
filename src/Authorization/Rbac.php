<?php

namespace Rad\Authorization;

use RuntimeException;
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
    protected static $roles = [];

    /**
     * Add role override if exists
     *
     * @param RoleInterface|string                 $role      Role name or object
     * @param ResourceCollection|array|string|null $resources Role resources
     */
    public static function addRole($role, $resources = null)
    {
        if ($role instanceof RoleInterface) {
            self::$roles[$role->getName()] = $role;
        } else {
            $role = new Role($role, $resources);
            self::$roles[$role->getName()] = $role;
        }
    }

    /**
     * Get role
     *
     * @param string $roleName Role name
     *
     * @return null|RoleInterface
     */
    public static function getRole($roleName)
    {
        if (!is_string($roleName)) {
            throw new InvalidArgumentException('Role name argument must be string.');
        }

        if (isset(self::$roles[$roleName])) {
            return self::$roles[$roleName];
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
    public static function hasRole($role)
    {
        if ($role instanceof RoleInterface) {
            $role = $role->getName();
        }

        if (!is_string($role)) {
            throw new InvalidArgumentException(
                'Role argument must be string or an object implemented "Rad\Authorization\Rbac\RoleInterface".'
            );
        }

        return isset(self::$roles[$role]);
    }

    /**
     * Role is granted
     *
     * @param RoleInterface|string     $role     Role name or object
     * @param ResourceInterface|string $resource Resource name or object
     *
     * @return bool
     */
    public static function isGranted($role, $resource)
    {
        if (self::hasRole($role)) {
            if ($role instanceof RoleInterface) {
                $role = $role->getName();
            }

            return self::$roles[$role]->hasResource($resource);
        }

        throw new RuntimeException(
            sprintf(
                'Role "%s" does not exists.',
                gettype($role) === 'string' ? $role : $role->getName()
            )
        );
    }
}
