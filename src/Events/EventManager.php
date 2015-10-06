<?php

namespace Rad\Events;

use Closure;
use SplPriorityQueue;

/**
 * RadPHP EventManager
 *
 * @package Rad\Event
 */
class EventManager
{
    /**
     * @var SplPriorityQueue[]
     */
    protected static $listener = [];

    /**
     * Attach listener
     *
     * @param string               $eventType
     * @param array|Closure|object $callable
     * @param int                  $priority
     *
     * @return EventManager
     */
    public function attach($eventType, $callable, $priority = 10)
    {
        if (!isset(self::$listener[$eventType])) {
            self::$listener[$eventType] = new SplPriorityQueue();
        }

        self::$listener[$eventType]->insert($callable, $priority);

        return $this;
    }

    /**
     * Detach listener
     *
     * @param string $eventType
     */
    public function detach($eventType)
    {
        if (isset(self::$listener[$eventType])) {
            unset(self::$listener[$eventType]);
        }
    }

    /**
     * Detach all listener
     */
    public function detachAll()
    {
        self::$listener = [];
    }

    /**
     * Detach all listener
     *
     * @param string $eventType
     *
     * @return bool
     */
    public function hasListener($eventType)
    {
        return isset(self::$listener[$eventType]);
    }

    /**
     * Add subscriber
     *
     * @param EventSubscriberInterface $subscriber
     *
     * @return EventManager
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $subscriber->subscribe($this);

        return $this;
    }

    /**
     * Dispatch event
     *
     * @param string $eventType
     * @param null   $subject
     * @param null   $data
     * @param bool   $cancelable
     *
     * @return Event
     */
    public function dispatch($eventType, $subject = null, $data = null, $cancelable = true)
    {
        $event = new Event($eventType, $subject, $data, $cancelable);

        if (isset(self::$listener[$eventType])) {
            $queue = clone self::$listener[$eventType];

            if (!$queue->isEmpty()) {
                $queue->top();

                while ($queue->valid()) {
                    $this->callListener($queue->current(), $event);

                    if ($event->isImmediatePropagationStopped()) {
                        break;
                    }

                    $queue->next();
                }
            }
        }

        return $event;
    }

    /**
     * Call listener
     *
     * @param array|Closure|object $callable
     * @param Event                $event
     */
    protected function callListener($callable, Event $event)
    {
        if ($callable instanceof Closure || is_array($callable)) {
            call_user_func_array($callable, array_filter([$event, $event->getSubject(), $event->getData()]));
        } elseif (is_object($callable)) {
            $callable->{$event->getType()}($event, $event->getSubject(), $event->getData());
        }
    }
}
