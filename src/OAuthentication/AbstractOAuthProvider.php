<?php

namespace Rad\OAuthentication;

use Psr\Http\Message\RequestInterface;

/**
 * Abstract OAuth Provider
 *
 * @package Rad\OAuthentication
 */
abstract class AbstractOAuthProvider implements ProviderInterface
{
    protected $clientId;
    protected $clientSecret;
    protected $scopes = [];
    protected $redirectUri;
    protected $state;

    /**
     * AbstractOAuthProvider constructor.
     *
     * @param string $clientId     Client id
     * @param string $clientSecret Client secret
     * @param array  $scopes       Scopes
     * @param string $redirectUri  Redirect uri
     * @param string $state        State
     */
    public function __construct($clientId, $clientSecret, array $scopes = [], $redirectUri = null, $state = null)
    {
        $this->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setScopes($scopes)
            ->setRedirectUri($redirectUri)
            ->setState($state);
    }

    /**
     * {@inheritdoc}
     */
    public function setClientId($clientId)
    {
        $this->clientId = strval($clientId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * {@inheritdoc}
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = strval($clientSecret);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = strval($redirectUri);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->state = strval($state);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Config http request
     *
     * @param RequestInterface $request Http request
     *
     * @return RequestInterface
     */
    protected function request(RequestInterface $request)
    {
        return $request->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', 'RadPHP-OAuth');
    }
}
