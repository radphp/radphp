<?php

namespace Rad\Network\Http\Response;

use InvalidArgumentException;
use Rad\Network\Http\Response;

/**
 * Redirect Response
 *
 * @package Rad\Network\Http\Response
 */
class RedirectResponse extends Response
{
    /**
     * Rad\Network\Http\Response\RedirectResponse constructor
     *
     * @param string $location
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     */
    public function __construct($location, $status = 302, $reason = '', array $headers = [])
    {
        if ($status < 300 || $status > 308) {
            throw new InvalidArgumentException(sprintf('Invalid redirection status code "%s".', $status));
        }

        $headers = $this->withHeader('Location', $location)->getHeaders();

        parent::__construct(null, $status, $reason, $headers);
    }

    /**
     * Factory method for chain ability.
     *
     * @param string $location
     * @param int    $status
     * @param string $reason
     * @param array  $headers
     *
     * @return Response
     */
    public static function create($location, $status = 302, $reason = '', array $headers = [])
    {
        return new static($location, $status, $reason, $headers);
    }
}
