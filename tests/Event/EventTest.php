<?php

namespace Rad\Event;

use PHPUnit_Framework_TestCase;
use Rad\TestEvent;

/**
 * Class EventTest
 *
 * @package Rad
 */
class EventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test Event
     */
    public function testEvent()
    {
        // load up an instance of the event handler
        $EventDispatcher = new EventDispatcher();

        // watch for event
        $EventDispatcher->onTest->attach(new TestEvent(), 'trigger');

        // trigger!
        $string = 'test';
        $EventDispatcher->onTest->notify($string);

        $this->assertEquals($string, 'your requested string is: test');
    }
}
