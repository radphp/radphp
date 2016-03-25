<?php

namespace Rad\Authorization\Tests\Rbac;

use PHPUnit_Framework_TestCase;
use Rad\Authorization\Rbac\Resource;
use Rad\Authorization\Rbac\ResourceCollection;

/**
 * ResourceCollection Test
 *
 * @package Rad\Authorization\Tests\Rbac
 */
class ResourceCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Iterator test
     */
    public function testIterator()
    {
        $resourceCollection = new ResourceCollection(
            [
                Resource::create('comments.add')
                    ->setTitle('Add comments')
                    ->setDescription('Add comments description'),
                Resource::create('comments.edit')
                    ->setTitle('Edit comments')
                    ->setDescription('Edit comments description')
            ]
        );

        $this->assertEquals($resourceCollection->count(), 2);

        while ($resourceCollection->valid()) {
            if (0 === $resourceCollection->key()) {
                $this->assertEquals(
                    $resourceCollection->current()->getTitle(),
                    'Add comments'
                );

                $this->assertEquals(
                    $resourceCollection->current()->getDescription(),
                    'Add comments description'
                );
            }

            $resourceCollection->next();
        }
    }

    /**
     * Remove test
     */
    public function testRemove()
    {
        $resourceCollection = new ResourceCollection(
            [
                new Resource('tags.add'),
                new Resource('tags.edit')
            ]
        );

        $resourceCollection->remove('tags.add');
        $this->assertFalse($resourceCollection->contains('tags.add'));

        $resourceCollection->removeAll();
        $this->assertFalse($resourceCollection->contains('tags.edit'));
        $this->assertEquals($resourceCollection->count(), 0);
    }
}
