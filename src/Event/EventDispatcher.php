<?php

namespace Rad\Event;

/**
 * Event Dispatcher
 *
 * @package Rad\Event
 */
class EventDispatcher
{
    // stores all created events
    private $events = [];

    /**
     * Determine the total number of events.
     *
     * @access	public
     * @return	int
     */
    public function count()
    {
        return count($this->events);
    }

    /**
     * Add a new event by name.
     *
     * @access	public
     * @param	string	$name
     * @param	mixed	$triggersMethod
     * @return	Event
     */
    public function add($name, $triggersMethod = NULL)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = new Event($triggersMethod);
        }
        return $this->events[$name];
    }

    /**
     * Retrieve an event by name. If one does not exist, it will be created
     * on the fly.
     *
     * @access	public
     * @param	string	$name
     * @return	Event
     */
    public function get($name)
    {
        return $this->add($name);
    }

    /**
     * Retrieves all events.
     *
     * @access	public
     * @return	array
     */
    public function getAll()
    {
        return $this->events;
    }

    /**
     * Trigger an event. Returns the event for monitoring status.
     *
     * @access	public
     * @param	string	$name
     * @param	mixed	$data	The data to pass to the triggered event(s)
     * @return	void
     */
    public function trigger($name, $data)
    {
        $this->get($name)->notify($data);
    }

    /**
     * Remove an event by name.
     *
     * @access	public
     * @param	string	$name
     * @return	bool
     */
    public function remove($name)
    {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
            return true;
        }
        return false;
    }

    /**
     * Retrieve the names of all current events.
     *
     * @access	public
     * @return	array
     */
    public function getNames()
    {
        return array_keys($this->events);
    }

    /**
     * Magic __get method for the lazy who don't wish to use the
     * add() or get() methods. It will add an event if it doesn't exist,
     * or simply return an existing event.
     *
     * @access	public
     * @return	Event
     */
    public function __get($name)
    {
        return $this->add($name);
    }
}
