<?php

namespace Rad\Core;

use Rad\Events\EventManager;
use Rad\Events\EventSubscriberInterface;
use Rad\DependencyInjection\ContainerAware;

/**
 * Responder
 *
 * @package Rad\Core
 */
abstract class Responder extends ContainerAware implements EventSubscriberInterface
{
    protected $data = [];

    /**
     * Set data
     *
     * @param string $name  Data name
     * @param mixed  $value Data value
     */
    public function setData($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Get data
     *
     * @param string $name Data name
     *
     * @return mixed
     */
    public function getData($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * Subscribe event listener
     *
     * @param EventManager $eventManager
     *
     * @return mixed
     */
    public function subscribe(EventManager $eventManager)
    {

    }
}
