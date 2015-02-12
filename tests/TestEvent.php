<?php

namespace Rad\Test;

use Rad\Event\EventListener;

class TestEvent extends EventListener
{
    public function trigger(&$string)
    {
        $string = 'your requested string is: ' . $string;
    }
}
