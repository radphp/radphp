<?php

namespace Rad\Events;

/**
 * Event Subscriber Interface
 *
 * @package Rad\Events
 */
interface EventSubscriberInterface
{
    /**
     * Subscribe event listener
     *
     * @param EventManager $eventManager
     *
     * @return mixed
     */
    public function subscribe(EventManager $eventManager);
}
