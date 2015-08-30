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
    protected static $eventManager;

    /**
     * Set event manager
     *
     * @param EventManager $eventManager
     */
    public static function setEventManager(EventManager $eventManager)
    {
        self::$eventManager = $eventManager;
    }

    /**
     * Get event manager
     *
     * @return EventManager
     */
    public static function getEventManager()
    {
        if (!self::$eventManager) {
            self::$eventManager = new EventManager();
        }

        return self::$eventManager;
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
    public static function dispatchEvent($eventType, $subject = null, $data = null, $cancelable = true)
    {
        return self::getEventManager()->dispatch($eventType, $subject, $data, $cancelable);
    }
}
