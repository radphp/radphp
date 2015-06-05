<?php

namespace Rad\Network\Http;

use Psr\Http\Message\RequestInterface;

/**
 * Http Client Interface
 *
 * @package Rad\Network\Http
 */
interface ClientInterface
{
    /**
     * Send request
     *
     * @param RequestInterface $request
     *
     * @return Response
     */
    public function send(RequestInterface $request);
}
