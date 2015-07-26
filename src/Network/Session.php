<?php

namespace Rad\Network;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use Serializable;
use SessionHandlerInterface;

/**
 * RadPHP Session
 *
 * @package Rad\Network
 */
class Session implements ArrayAccess, Iterator, Serializable, JsonSerializable, Countable
{
    protected $started = false;

    /**
     * Rad\Network\Session constructor
     *
     * @param SessionHandlerInterface $handler
     */
    public function __construct(SessionHandlerInterface $handler = null)
    {
        if ($handler) {
            session_set_save_handler($handler);
        }
    }

    /**
     * Magic sets a session variable
     *
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Magic gets a session variable
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Magic check whether a session variable is set
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Magic removes a session variable
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * Rad\Network\Session destructor
     */
    public function __destruct()
    {
        return $this->destroy();
    }

    /**
     * Starts the session (if headers are already sent the session will not be started)
     *
     * @return boolean
     */
    public function start()
    {
        if (headers_sent() === false) {
            session_start();
            $this->started = true;

            return true;
        }

        return false;
    }

    /**
     * Gets a session variable
     *
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return $defaultValue;
    }

    /**
     * Sets a session variable
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check whether a session variable is set
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Removes a session variable
     *
     * @param string $key
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Set the current session id
     *
     * @param string $id
     */
    public function setId($id)
    {
        session_id($id);
    }

    /**
     * Returns active session id
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Update the current session id with a newly generated one
     *
     * @param bool $deleteOldSession Whether to delete the old associated session file or not.
     *
     * @link http://php.net/manual/en/function.session-regenerate-id.php
     * @return bool true on success or false on failure.
     */
    public function regenerateId($deleteOldSession = false)
    {
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Set the current session name
     *
     * @param string $name The session name references the name of the session, which is
     *                     used in cookies and URLs (e.g. PHPSESSID). It
     *                     should contain only alphanumeric characters; it should be short and
     *                     descriptive (i.e. for users with enabled cookie warnings).
     *                     If "name" is specified, the name of the current
     *                     session is changed to its value.
     *                     The session name can't consist of digits only, at least one letter
     *                     must be present. Otherwise a new session id is generated every time.
     *
     * @link http://php.net/manual/en/function.session-name.php
     * @return string the name of the current session.
     */
    public function setName($name)
    {
        session_name($name);
    }

    /**
     * Get the current session name
     *
     * @link http://php.net/manual/en/function.session-name.php
     * @return string the name of the current session.
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Check whether the session has been started
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Destroys the active session
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->started === true) {
            $this->started = false;
            return session_destroy();
        }

        return false;
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @return boolean true on success or false on failure. The return value
     * will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($_SESSION);
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($_SESSION);
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($_SESSION);
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($_SESSION);
    }

    /**
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($_SESSION);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized The string representation of the object.
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @return void
     */
    public function unserialize($serialized)
    {
        $_SESSION = unserialize($serialized);
    }

    /**
     * Count elements of an object
     *
     * @return int The custom count as an integer. The return value is cast to an integer.
     * @link http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        return count($_SESSION);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by json_encode,
     *       which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $_SESSION;
    }
}
