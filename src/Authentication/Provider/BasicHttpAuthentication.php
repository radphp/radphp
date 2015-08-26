<?php

namespace Rad\Authentication\Provider;

use Rad\Network\Http\Response;
use Rad\Network\Http\Message\ServerRequest;
use Rad\Authentication\AbstractAuthenticationProvider;

/**
 * Basic Http Authentication
 *
 * @package Rad\Authentication\Provider
 */
class BasicHttpAuthentication extends AbstractAuthenticationProvider
{
    /**
     * Rad\Authentication\Provider\BasicHttpAuthentication constructor
     *
     * @param ServerRequest $request
     */
    public function __construct(ServerRequest $request)
    {
        if (isset($request->getServerParams()['PHP_AUTH_USER'])) {
            $this->identity = $request->getServerParams()['PHP_AUTH_USER'];
        }

        if (isset($request->getServerParams()['PHP_AUTH_PW'])) {
            $this->credential = $request->getServerParams()['PHP_AUTH_PW'];
        }

        parent::__construct($this->identity, $this->credential);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        if (empty($this->identity)) {
            return false;
        }

        return $this->repository->findUser($this->identity, $this->credential);
    }
}
