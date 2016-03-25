<?php

namespace Rad\Authorization\Tests;

use PHPUnit_Framework_TestCase;
use Rad\Authorization\Rbac;
use Rad\Authorization\Rbac\ResourceCollection;

/**
 * Rbac Test
 *
 * @package Rad\Authorization\Tests
 */
class RbacTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->rbac = new Rbac();
    }

    /**
     * Test addRole method
     */
    public function testAddRole()
    {
        $this->rbac->addRole('string_role', ['posts.add', new Rbac\Resource('posts.edit')]);
        $this->assertEquals('string_role', $this->rbac->getRole('string_role')->getName());

        $role = new Rbac\Role('object_role', new Rbac\ResourceCollection(['users.add']));
        $role->setName('object_role2')
            ->setTitle('Role Title')
            ->setDescription('Role description');

        $this->rbac->addRole($role);
        $this->assertNull($this->rbac->getRole('object_role'));

        $this->assertEquals('object_role2', $this->rbac->getRole('object_role2')->getName());
        $this->assertEquals('Role Title', $this->rbac->getRole('object_role2')->getTitle());
        $this->assertEquals('Role description', $this->rbac->getRole('object_role2')->getDescription());

        $this->setExpectedExceptionRegExp('InvalidArgumentException', '/Role name must be string\./');
        $role->setName(1);
    }

    /**
     * Test getRole method
     */
    public function testGetRole()
    {
        $role = Rbac\Role::create('string_role');
        $this->rbac->addRole($role);
        $this->assertEquals('string_role', $this->rbac->getRole('string_role')->getName());

        $this->assertNull($this->rbac->getRole('not_exists_role'));

        $this->setExpectedExceptionRegExp('InvalidArgumentException', '/Role name argument must be string./');
        $this->rbac->getRole(1);
    }

    /**
     * Test hasRole method
     */
    public function testHasRole()
    {
        $this->rbac->addRole($role = new Rbac\Role('foo'));
        $this->assertTrue($this->rbac->hasRole('foo'));
        $this->assertTrue($this->rbac->hasRole($role));

        $notAddedRole = new Rbac\Role('alice');
        $this->assertFalse($this->rbac->hasRole($notAddedRole));
        $this->assertFalse($this->rbac->hasRole('alice'));

        $this->setExpectedExceptionRegExp(
            'InvalidArgumentException',
            '/Role argument must be string or an object implemented "Rad\\\Authorization\\\Rbac\\\RoleInterface"\./'
        );

        $this->rbac->hasRole(new \stdClass());
        $this->rbac->hasRole(2);
    }

    public function testIsGranted()
    {
        $collection = new ResourceCollection(['comments.add', 'comments.edit']);
        $adminRole = new Rbac\Role('admin', ['list_user', Rbac\Resource::create('delete_user')]);
        $this->rbac->addRole($adminRole);

        $adminRole->setResources($collection);
        $this->assertTrue($adminRole->hasResource('comments.add'));
        $this->assertTrue($adminRole->hasResource('comments.edit'));
        $this->assertFalse($adminRole->hasResource('posts.add'));

        $this->assertFalse($this->rbac->isGranted('admin', 'list_user'));
        $this->assertFalse($this->rbac->isGranted($adminRole, 'delete_user'));

        $notExistsRole = new Rbac\Role('notExistsRole');
        $this->setExpectedExceptionRegExp(
            'RuntimeException',
            sprintf('/Role "%s" does not exists./', preg_quote($notExistsRole->getName(), '/'))
        );
        $this->assertTrue($this->rbac->isGranted($notExistsRole->getName(), 'delete_user'));
    }
}
