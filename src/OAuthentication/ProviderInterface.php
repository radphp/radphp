<?php

namespace Rad\OAuthentication;

/**
 * Provider Interface
 *
 * @package Rad\OAuthentication
 */
interface ProviderInterface
{
    /**
     * Set client id
     *
     * @param string $clientId Client id
     *
     * @return self
     */
    public function setClientId($clientId);

    /**
     * Get client id
     *
     * @return string
     */
    public function getClientId();

    /**
     * Get client secret
     *
     * @param string $clientSecret Client secret
     *
     * @return self
     */
    public function setClientSecret($clientSecret);

    /**
     * Get client secret
     *
     * @return string
     */
    public function getClientSecret();

    /**
     * Set scopes
     *
     * @param array $scopes Scopes
     *
     * @return self
     */
    public function setScopes(array $scopes);

    /**
     * Get scopes
     *
     * @return array
     */
    public function getScopes();

    /**
     * Get redirect uri
     *
     * @param string $redirectUri Redirect uri
     *
     * @return self
     */
    public function setRedirectUri($redirectUri);

    /**
     * Get redirect uri
     *
     * @return string
     */
    public function getRedirectUri();

    /**
     * Get state
     *
     * @param string $state State
     *
     * @return self
     */
    public function setState($state);

    /**
     * Get state
     *
     * @return string
     */
    public function getState();


    /**
     * Get authorize uri
     *
     * @return string
     */
    public function getAuthorizeUri();

    /**
     * Get access token
     *
     * @param string $authorizeCode Authorize code
     *
     * @return array
     */
    public function getAccessToken($authorizeCode);

    /**
     * Get user detail
     *
     * @param string $token Access token
     *
     * @return User
     * @throws Exception
     */
    public function getUser($token);
}
