<?php

namespace Rad\OAuthentication\Provider;

use Rad\Network\Http\Client\Curl;
use Rad\Network\Http\Message\Request;
use Rad\OAuthentication\User;
use Rad\OAuthentication\Exception;
use Rad\OAuthentication\AbstractOAuthProvider;

/**
 * OAuth GitHub Provider
 *
 * @package Rad\OAuthentication\Provider
 */
class GitHubProvider extends AbstractOAuthProvider
{
    protected $scopeDelimiter = ',';

    /**
     * Grants read/write access to profile info only.
     * Note that this scope includes user:email and user:follow.
     */
    const SCOPE_USER = 'user';

    /**
     * Grants read access to a user’s email addresses.
     */
    const SCOPE_USER_EMAIL = 'user:email';

    /**
     * Grants access to follow or unfollow other users.
     */
    const SCOPE_USER_FOLLOW = 'user:follow';

    /**
     * Grants read/write access to code, commit statuses, collaborators,
     * and deployment statuses for public repositories and organizations.
     * Also required for starring public repositories.
     */
    const SCOPE_PUBLIC_REPO = 'public_repo';

    /**
     * Grants read/write access to code, commit statuses, collaborators,
     * and deployment statuses for public and private repositories and organizations.
     */
    const SCOPE_REPO = 'repo';

    /**
     * Grants access to deployment statuses for public and private repositories.
     * This scope is only necessary to grant other users or services access to deployment
     * statuses, without granting access to the code.
     */
    const SCOPE_REPO_DEPLOYMENT = 'repo_deployment';

    /**
     * Grants read/write access to public and private repository commit statuses.
     * This scope is only necessary to grant other users or services access to
     * private repository commit statuses without granting access to the code.
     */
    const SCOPE_REPO_STATUS = 'repo:status';

    /**
     * Grants access to delete adminable repositories.
     */
    const SCOPE_DELETE_REPO = 'delete_repo';

    /**
     * Grants read access to a user’s notifications. repo also provides this access.
     */
    const SCOPE_NOTIFICATIONS = 'notifications';

    /**
     * Grants write access to gists.
     */
    const SCOPE_GIST = 'gist';

    /**
     * Grants read and ping access to hooks in public or private repositories.
     */
    const SCOPE_READ_REPO_HOOK = 'read:repo_hook';

    /**
     * Grants read, write, and ping access to hooks in public or private repositories.
     */
    const SCOPE_WRITE_REPO_HOOK = 'write:repo_hook';

    /**
     * Grants read, write, ping, and delete access to hooks in public or private repositories.
     */
    const SCOPE_ADMIN_REPO_HOOK = 'admin:repo_hook';

    /**
     * Grants read, write, ping, and delete access to organization hooks.
     * Note: OAuth tokens will only be able to perform these actions on organization
     * hooks which were created by the OAuth application.
     * Personal access tokens will only be able to perform these actions on
     * organization hooks created by a user.
     */
    const SCOPE_ADMIN_ORG_HOOK = 'admin:org_hook';

    /**
     * Read-only access to organization, teams, and membership.
     */
    const SCOPE_READ_ORG = 'read:org';

    /**
     * Publicize and unpublicize organization membership.
     */
    const SCOPE_WRITE_ORG = 'write:org';

    /**
     * Fully manage organization, teams, and memberships.
     */
    const SCOPE_ADMIN_ORG = 'admin:org';

    /**
     * List and view details for public keys.
     */
    const SCOPE_READ_PUBLIC_KEY = 'read:public_key';

    /**
     * Create, list, and view details for public keys.
     */
    const SCOPE_WRITE_PUBLIC_KEY = 'write:public_key';

    /**
     * Fully manage public keys.
     */
    const SCOPE_ADMIN_PUBLIC_KEY = 'admin:public_key';

    const AUTHORIZE_URI = 'https://github.com/login/oauth/authorize';
    const TOKEN_URI = 'https://github.com/login/oauth/access_token';
    const USER_API_URI = 'https://api.github.com/user';

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
                        'client_id' => $this->getConfig()->getClientId(),
                        'client_secret' => $this->getConfig()->getClientSecret(),
                        'code' => $_GET['code']
                    ]
                ]
            );

            $response = $client->send($request);
            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result['access_token'])) {
                if (isset($result['error'])) {
                    throw new Exception(
                        sprintf(
                            '[%s] %s (%s)',
                            $result['error'],
                            $result['error_description'],
                            $result['error_uri']
                        )
                    );
                }

                throw new Exception('Access token not found');
            }

            return $result;
        } else {
            if (isset($_GET['error'])) {
                throw new Exception(
                    sprintf(
                        '[%s] %s (%s)',
                        $_GET['error'],
                        $_GET['error_description'],
                        $_GET['error_uri']
                    )
                );
            }

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

        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                sprintf(
                    '%s (%s)',
                    $result['message'],
                    $result['documentation_url']
                )
            );
        }

        return (new User($result))
            ->setId(isset($result['id']) ? $result['id'] : null)
            ->setName(isset($result['name']) ? $result['name'] : null)
            ->setUsername(isset($result['login']) ? $result['login'] : null)
            ->setAvatarUri(isset($result['avatar_url']) ? $result['avatar_url'] : null)
            ->setEmail($this->getEmail($token));
    }

    /**
     * Get user email
     *
     * @param string $token Access token
     *
     * @return null|string
     * @throws Exception
     */
    protected function getEmail($token)
    {
        if (in_array(self::SCOPE_USER_EMAIL, $this->getConfig()->getScope())) {
            $request = new Request(self::USER_API_URI . '/emails?access_token=' . $token, Request::METHOD_GET);
            $request = $this->request($request);

            $client = new Curl();
            $response = $client->send($request);
            $result = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200) {
                throw new Exception(
                    sprintf(
                        '%s (%s)',
                        $result['message'],
                        $result['documentation_url']
                    )
                );
            }

            foreach ($result as $email) {
                if ($email['primary'] && $email['verified']) {
                    return $email['email'];
                }
            }
        }

        return null;
    }
}
