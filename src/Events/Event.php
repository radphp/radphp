<?php

namespace Rad\Events;

/**
 * RadPHP Event
 *
 * @package Rad\Event
 */
class Event
{
    protected $type;
    protected $subject;
    protected $data;
    protected $result;
    protected $cancelable = true;
    protected $immediateStopped = false;

    /**
     * Rad\Event\Event constructor
     *
     * @param string $type       Event type
     * @param null   $subject    Subject
     * @param mixed  $data       Data
     * @param bool   $cancelable Event is cancelable
     */
    function __construct($type, $subject = null, $data = null, $cancelable = true)
    {
        $this->type = $type;
        $this->subject = $subject;
        $this->data = $data;
        $this->cancelable = (bool)$cancelable;
    }

    /**
     * Check event is cancelable
     *
     * @return boolean
     */
    public function isCancelable()
    {
        return $this->cancelable;
    }

    /**
     * Keeps the rest of the handlers from being executed and prevents the event
     *
     * @throws Exception
     */
    public function stopImmediatePropagation()
    {
        if ($this->isCancelable() === false) {
            throw new Exception(sprintf('Event "%s" not cancelable.', $this->name));
        }

        $this->immediateStopped = true;
    }

    /**
     * Returns whether Event::stopImmediatePropagation() was ever called on this event object.
     *
     * @return bool
     */
    public function isImmediatePropagationStopped()
    {
        return $this->immediateStopped;
    }

    /**
     * Set the last value returned by an event handler that was triggered by this event.
     *
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Get the last value returned by an event handler that was triggered by this event.
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get event type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get event subject
     *
     * @return null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get event data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
