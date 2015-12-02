<?php

namespace Rad\Authentication;

use Closure;
use RuntimeException;

/**
 * User Details
 *
 * @package Rad\Authentication
 */
class UserDetails
{
    /**
     * @var Closure
     */
    protected $callback;

    /**
     * Rad\Authentication\UserDetails constructor.
     *
     * @param Closure $callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Get details
     *
     * @param array $data
     *
     * @return mixed
     */
    public function getDetails(array $data)
    {
        $result = call_user_func($this->callback, $data);

        if (false === $result) {
            throw new RuntimeException('Occurred error.');
        }

        return $result;
    }
}
