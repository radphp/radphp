<?php

namespace Rad\Authentication\OAuth\Provider;

use Rad\Network\Http\Client\Curl;
use Rad\Network\Http\Message\Request;
use Rad\Authentication\OAuth\User;
use Rad\Authentication\OAuth\Exception;
use Rad\Authentication\OAuth\AbstractOAuthProvider;

/**
 * OAuth Google Provider
 *
 * @package Rad\Authentication\OAuth\Provider
 */
class GoogleProvider extends AbstractOAuthProvider
{
    protected $scopeDelimiter = ' ';

    const AUTHORIZE_URI = 'https://accounts.google.com/o/oauth2/auth';
    const TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';
    const USER_API_URI = 'https://www.googleapis.com/plus/v1/people/me';

    /**
     * {@inheritdoc}
     */
    public function getAuthorizeUri()
    {
        $query = http_build_query(
            [
                'client_id' => $this->getConfig()->getClientId(),
                'redirect_uri' => $this->getConfig()->getRedirectUri(),
                'scope' => implode($this->scopeDelimiter, $this->getConfig()->getScope()),
                'response_type' => 'code',
            ]
        );

        $uri = self::AUTHORIZE_URI . '?' . $query;

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        if (isset($_GET['code'])) {
            $request = new Request(self::TOKEN_URI, Request::METHOD_POST);
            $request = $this->request($request);

            $client = new Curl(
                [
                    CURLOPT_POSTFIELDS => [
                        'code' => $_GET['code'],
                        'client_id' => $this->getConfig()->getClientId(),
                        'client_secret' => $this->getConfig()->getClientSecret(),
                        'redirect_uri' => $this->getConfig()->getRedirectUri(),
                        'grant_type' => 'authorization_code'
                    ]
                ]
            );

            $response = $client->send($request);
            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['access_token'])) {
                if (isset($result['error'])) {
                    throw new Exception(
                        sprintf(
                            '[%s] %s',
                            $result['error'],
                            $result['error_description']
                        )
                    );
                }

                throw new Exception('Access token not found.');
            }

            return $result;
        } else {
            throw new Exception('Authorize code not found.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($token)
    {
        $request = new Request(self::USER_API_URI . '?access_token=' . $token, Request::METHOD_GET);
        $request = $this->request($request);

        $client = new Curl();
        $response = $client->send($request);
        $result = json_decode($response->getBody()->getContents(), true);

        if (isset($result['error'])) {
            throw new Exception(
                sprintf(
                    '[%s] %s',
                    $result['error']['code'],
                    $result['error']['message']
                )
            );
        }

        return (new User($result))
            ->setId(isset($result['id']) ? $result['id'] : null)
            ->setEmail($result['emails'] ? $result['emails'][0]['value'] : null)
            ->setAvatarUri($result['image'] ? $result['image']['url'] : null)
            ->setUsername($result['emails'] ? $result['emails'][0]['value'] : null)
            ->setName(isset($result['displayName']) ? $result['displayName'] : null);
    }
}
