<?php

namespace Rad\Network\Session;

use InvalidArgumentException;
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Network\Session;

/**
 * Session Bag
 *
 * @package Rad\Network\Session
 */
class SessionBag implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Bag name
     *
     * @var string
     */
    protected $name;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Bag data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Session bag is initialized
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * Rad\Network\Session\SessionBag constructor
     *
     * @param string $name Bag name
     */
    public function __construct($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Session bag name must be string.');
        }

        $this->name = $name;
    }

    /**
     * Initialize session bag
     *
     * @throws Exception
     * @throws \Rad\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function initialize()
    {
        if (false === $this->initialized) {
            if (!$this->container) {
                throw new Exception('A container object is required to access the \'session\' service.');
            }

            $this->session = $this->container->get('session');
            $this->data = $this->session->get($this->name, []);

            $this->initialized = true;
        }
    }

    /**
     * Set in bag
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws Exception
     */
    public function set($key, $value)
    {
        $this->initialize();

        $ids = explode('.', $key);
        $base = &$this->data;

        while ($current = array_shift($ids)) {
            if (is_array($base) && array_key_exists($current, $base)) {
                $base = &$base[$current];
            } else {
                $base[$current] = [];
                $base = &$base[$current];
            }
        }

        $base = $value;
        $this->session->set($this->name, $this->data);
    }

    /**
     * Get from bag
     *
     * @param string $key
     *
     * @return array|null
     * @throws Exception
     */
    public function get($key)
    {
        $this->initialize();

        $ids = explode('.', $key);
        $base = &$this->data;

        while ($current = array_shift($ids)) {
            if (is_array($base) && array_key_exists($current, $base)) {
                $base = &$base[$current];
            } else {
                return null;
            }
        }

        return $base;
    }

    /**
     * Key exist
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }

    /**
     * Destroy bag
     */
    public function destroy()
    {
        $this->session->remove($this->name);
    }

    /**
     * Set container
     *
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
