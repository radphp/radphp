<?php

namespace Rad\Network\Session\Flash;

use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Network\Session\Exception;
use Rad\Network\Session\SessionBag;

/**
 * Flash Bag
 *
 * @package Rad\Network\Session\Flash
 */
class FlashBag implements ContainerAwareInterface
{
    /**
     * Session bag name
     *
     * @var string
     */
    protected $bagName;

    /**
     * Messages
     *
     * @var array
     */
    protected $messages = [];

    /**
     * @var SessionBag
     */
    protected $sessionBag;

    /**
     * @var Container
     */
    protected $container;

    const BAG_NAME = '__flash_messages__';
    const TYPE_SUCCESS = 'success';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER = 'danger';

    /**
     * Rad\Network\Session\Flash\FlashBag constructor
     *
     * @param string $bagName
     */
    public function __construct($bagName = self::BAG_NAME)
    {
        if (empty($bagName)) {
            $bagName = self::BAG_NAME;
        }

        $this->bagName = $bagName;
    }

    /**
     * Get session bag
     *
     * @throws Exception
     * @throws \Rad\DependencyInjection\Exception\ServiceNotFoundException
     *
     * @return SessionBag
     */
    public function getSessionBag()
    {
        if (null === $this->sessionBag) {
            if (!$this->container) {
                throw new Exception('A container object is required to access the \'session_bag\' service.');
            }

            $this->sessionBag = $this->container->get('session_bag', [$this->bagName]);
        }

        return $this->sessionBag;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Add message
     *
     * @param string $message
     * @param string $type
     */
    public function add($message, $type)
    {
        $this->messages[$type][] = $message;

        $this->getSessionBag()->set($type, $this->messages[$type]);
    }

    /**
     * Set flash messages
     *
     * @param array  $messages
     * @param string $type
     */
    public function set(array $messages, $type)
    {
        $this->getSessionBag()->set($type, (array)$messages);
    }

    /**
     * Is type exist
     *
     * @param string $type
     *
     * @return bool
     */
    public function has($type)
    {
        return array_key_exists($type, $this->messages) && $this->messages[$type];
    }

    /**
     * Peek type messages
     *
     * @param string $type
     * @param array  $default
     * @param bool   $remove
     *
     * @return array
     */
    public function get($type, array $default = [], $remove = true)
    {
        if ($this->has($type)) {
            if (true === $remove) {
                unset($this->messages[$type]);
            }

            return $this->messages[$type];
        }

        return $default;
    }

    /**
     * Get all messages and remove theirs
     *
     * @param bool $remove Remove all messages
     *
     * @return array
     */
    public function getAll($remove = true)
    {
        $output = $this->messages;
        if (true === $remove) {
            $this->clear();
        }

        return $output;
    }

    /**
     * Clear all messages
     */
    public function clear()
    {
        $this->messages = [];
        $this->getSessionBag()->destroy();
    }

    /**
     * Add success message
     *
     * @param string $message
     */
    public function success($message)
    {
        $this->add($message, self::TYPE_SUCCESS);
    }

    /**
     * Add info message
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->add($message, self::TYPE_INFO);
    }

    /**
     * Add warning message
     *
     * @param string $message
     */
    public function warning($message)
    {
        $this->add($message, self::TYPE_WARNING);
    }

    /**
     * Add danger message
     *
     * @param string $message
     */
    public function danger($message)
    {
        $this->add($message, self::TYPE_DANGER);
    }
}
