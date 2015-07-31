<?php

namespace Rad\Authentication\OAuth;

use Rad\Utility\Inflection;

/**
 * OAuth Provider Factory
 *
 * @package Rad\Authentication\OAuth
 */
class OAuthProviderFactory
{
    /**
     * Create OAuth provider
     *
     * @param string $oAuthProvider
     *
     * @return AbstractOAuthProvider
     * @throws Exception
     */
    public static function create($oAuthProvider)
    {
        if (strpos($oAuthProvider, '\\') !== false) {
            if (class_exists($oAuthProvider)) {
                $providerInstance = new $oAuthProvider();
                if (!($providerInstance instanceof AbstractOAuthProvider)) {
                    throw new Exception(
                        sprintf(
                            'OAuth provider "%s" must be extend "AbstractOAuthProvider".',
                            $oAuthProvider
                        )
                    );
                }

                return $providerInstance;
            } else {
                throw new Exception(sprintf('OAuth provider "%s" does not exist.', $oAuthProvider));
            }
        }

        $oAuthClass = 'Rad\\Security\\Authentication\\OAuth\\Provider\\' .
            Inflection::camelize($oAuthProvider) . 'Provider';

        if (class_exists($oAuthClass)) {
            return new $oAuthClass();
        } else {
            throw new Exception(sprintf('OAuth provider "%s" does not exist.', $oAuthClass));
        }
    }
}
