<?php

namespace Rad\Event;

use SplObjectStorage;
use SplObserver;
use SplSubject;

class Event implements SplSubject
{
    // stores all attached observers
    private $observers;

    /**
     * Default constructor to initialize the observers.
     */
    public function __construct()
    {
        $this->observers = new SplObjectStorage();
    }

    /**
     * Wrapper for the attach method, allowing for the addition
     * of a method name to call within the observer.
     *
     * @param    SplObserver $event
     * @param    mixed       $triggersMethod
     *
     * @return    Event
     */
    public function attach(SplObserver $event, $triggersMethod = null)
    {
        $this->observers->attach($event, $triggersMethod);
        return $this;
    }

    /**
     * Detach an existing observer from the particular event.
     *
     * @param    SplObserver $event
     *
     * @return    Event
     */
    public function detach(SplObserver $event)
    {
        $this->observers->detach($event);
        return $this;
    }

    /**
     * Notify all event observers that the event was triggered.
     *
     * @param    mixed &$args
     */
    public function notify(&$args = null)
    {
        $this->observers->rewind();
        while ($this->observers->valid()) {
            $triggersMethod = $this->observers->getInfo();
            $observer = $this->observers->current();
            $observer->update($this, $triggersMethod, $args);

            // on to the next observer for notification
            $this->observers->next();
        }
    }
}
