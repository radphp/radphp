<?php

namespace Rad\Event;

use Exception;
use SplObserver;
use SplSubject;

abstract class EventListener implements SplObserver
{
    // holds all states
    private $states = [];

    /**
     * Returns all states.
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Adds a new state.
     *
     * @param    mixed $state
     * @param    int $stateValue
     * @return    void
     */
    public function addState($state, $stateValue = 1)
    {
        $this->states[$state] = $stateValue;
    }

    /**
     * @Removes   a state.
     *
     * @param    mixed $state
     * @return    bool
     */
    public function removeState($state)
    {
        if ($this->hasState($state)) {
            unset($this->states[$state]);
        }

        return true;
    }

    /**
     * Checks if a given state exists.
     *
     * @param    mixed $state
     * @return    bool
     */
    public function hasState($state)
    {
        return isset($this->states[$state]);
    }

    /**
     * Implementation of SplObserver::update().
     *
     * @param    SplSubject $subject
     * @param    mixed $triggersMethod
     * @param    mixed &$arg Any passed in arguments
     * @throws Exception
     */
    public function update(SplSubject $subject, $triggersMethod = null, &$arg = null)
    {
        if (!$triggersMethod) {
            throw new Exception('The specified event method ' . get_called_class() . '::' . 'update() does not exist.');
        }

        if (!method_exists($this, $triggersMethod)) {
            throw new Exception('The specified event method ' . get_called_class() . '::' . $triggersMethod . ' does not exist.');
        }

        $this->{$triggersMethod}($arg);
    }

}

