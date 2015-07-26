<?php

namespace Rad\Events;

/**
 * Event Manager Trait
 *
 * @package Rad\Events
 */
trait EventManagerTrait
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Set event manager
     *
     * @param EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Get event manager
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = new EventManager();
        }

        return $this->eventManager;
    }

    /**
     * Dispatch event
     *
     * @param string      $eventType
     * @param null|object $subject
     * @param mixed       $data
     * @param bool        $cancelable
     *
     * @return Event
     */
    public function dispatchEvent($eventType, $subject = null, $data = null, $cancelable = true)
    {
        if ($subject === null) {
            $subject = $this;
        }

        return $this->getEventManager()->dispatch($eventType, $subject, $data, $cancelable);
    }
}
