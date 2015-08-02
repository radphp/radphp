<?php

namespace Rad\Authentication\Storage;

use Rad\Network\Session;
use Rad\Authentication\StorageInterface;

/**
 * Session Storage
 *
 * @package Rad\Authentication\Storage
 */
class SessionStorage implements StorageInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var string
     */
    protected $namespace;

    const NAMESPACE_AUTH = 'RadAuth';

    /**
     * Rad\Authentication\Storage\SessionStorage constructor
     *
     * @param Session $session
     * @param string  $namespace
     */
    public function __construct(Session $session, $namespace = self::NAMESPACE_AUTH)
    {
        $this->session = $session;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->session->get($this->namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function write($data)
    {
        $this->session->set($this->namespace, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->session->remove($this->namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function exist()
    {
        return $this->session->has($this->namespace);
    }
}
