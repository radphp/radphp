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
     * @var ServerRequest
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Rad\Authentication\Provider\BasicHttpAuthentication constructor
     *
     * @param ServerRequest $request
     * @param Response      $response
     */
    public function __construct(ServerRequest $request, Response $response)
    {
        if (isset($request->getServerParams()['PHP_AUTH_USER'])) {
            $this->identity = $request->getServerParams()['PHP_AUTH_USER'];
        }

        if (isset($request->getServerParams()['PHP_AUTH_PW'])) {
            $this->credential = $request->getServerParams()['PHP_AUTH_PW'];
        }

        $this->request = $request;
        $this->response = $response;

        parent::__construct($this->identity, $this->credential);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        if (!empty($this->identity)) {
            $this->response->setHeader('WWW-Authenticate', 'Basic realm="My Realm"')
                ->setStatusCode(401);

            return false;
        }

        return $this->repository->findUser($this->identity, $this->credential);
    }
}
