<?php

namespace Rad\Core;

use Rad\Events\EventManager;
use Rad\Events\EventSubscriberInterface;
use Rad\Network\Http\RequestInterface;
use Rad\Network\Http\ResponseInterface;

/**
 * Responder
 *
 * @package Rad\Core
 */
abstract class Responder implements EventSubscriberInterface
{
    protected $request;
    protected $response;
    protected $data = [];

    /**
     * Responder constructor
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

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
