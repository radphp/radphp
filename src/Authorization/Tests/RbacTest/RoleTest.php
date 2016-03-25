<?php

namespace Rad\Authorization\Tests\Rbac;

use PHPUnit_Framework_TestCase;
use Rad\Authorization\Rbac\Resource;
use Rad\Authorization\Rbac\ResourceCollection;
use Rad\Authorization\Rbac\Role;

/**
 * Role Test
 *
 * @package Rad\Authorization\Tests\Rbac
 */
class RoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test resource
     */
    public function testResource()
    {
        $role = new Role('admin');
        $role->addResource('users.list');
        $this->assertTrue($role->hasResource('users.list'));
        $this->assertFalse($role->hasResource('users.does_not_have_resource'));


        $role->setResources(
            [
                'posts.add',
                new Resource('posts.edit')
            ]
        );
        $this->assertTrue($role->hasResource('posts.add'));
        $this->assertTrue($role->hasResource('posts.edit'));
        $this->assertFalse($role->hasResource('users.list'));

        $collection = new ResourceCollection(['comments.add', 'comments.edit']);
        $role->setResources($collection);
        $this->assertTrue($role->hasResource('comments.add'));
        $this->assertTrue($role->hasResource('comments.edit'));
        $this->assertFalse($role->hasResource('posts.add'));

        $this->assertTrue(spl_object_hash($collection) === spl_object_hash($role->getResources()));
    }
}
