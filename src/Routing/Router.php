<?php

namespace Rad\Routing;

use Rad\Config;
use Rad\Core\Bundles;

/**
 * RadPHP Router
 *
 * @package Rad\Routing
 */
class Router
{
    protected $uriSource = self::URI_SOURCE_SERVER_REQUEST_URI;
    protected $module;
    protected $action;
    protected $actionNamespace;
    protected $responderNamespace;
    protected $params;
    protected $wasMatched = false;

    const URI_SOURCE_GET_URL = 'get_url_source';
    const URI_SOURCE_SERVER_REQUEST_URI = 'request_uri_source';

    /**
     * Get rewrite info. This info is read from $_GET['_url'].
     * This returns '/' if the rewrite information cannot be read
     *
     * @return string
     */
    public function getRewriteUri()
    {
        if ($this->uriSource !== self::URI_SOURCE_SERVER_REQUEST_URI) {
            if (isset($_GET['_url']) && !empty($_GET['_url'])) {
                return $_GET['_url'];
            }
        } else {
            if (isset($_SERVER['REQUEST_URI'])) {
                $requestUri = explode('?', $_SERVER['REQUEST_URI']);
                if (!empty($requestUri[0])) {
                    return $requestUri[0];
                }
            }
        }

        return '/';
    }

    /**
     * Handles routing information received from the rewrite engine
     *
     * @param string $uri
     */
    public function handle($uri = null)
    {
        if ($uri) {
            $realUri = $uri;
        } else {
            $realUri = $this->getRewriteUri();
        }

        $realUri = trim($realUri, '/');
        $parts = explode('/', $realUri);

        // Cleaning route parts & Rebase array keys
        $parts = array_values(array_filter($parts, 'trim'));
        $module = str_replace(' ', '', ucwords(str_replace('_', ' ', reset($parts))));
        $bundles = array_intersect(['App', $module], Bundles::getLoaded());

        $matchedRoutes = [];
        foreach ($bundles as $bundleName) {
            $bundleNamespace['action'] = Bundles::getNamespace($bundleName) . 'Action';
            $bundleNamespace['responder'] = Bundles::getNamespace($bundleName) . 'Responder';
            foreach ($parts as $key => $part) {
                if ($bundleName !== 'App' && $key == 0) {
                    continue;
                }

                $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $part)));
                $bundleNamespace['action'] .= '\\' . $camel;
                $bundleNamespace['responder'] .= '\\' . $camel;
                $namespace = $bundleNamespace['action'] . 'Action';
                $responderNS = $bundleNamespace['responder'] . 'Responder';

                if (class_exists($namespace)) {
                    $matchedRoutes[] = [
                        'namespace' => $namespace,
                        'responder' => $responderNS,
                        'action' => $part,
                        'params' => array_slice($parts, $key + 1)
                    ];
                }
            }
        }

        if ($firstRoute = reset($matchedRoutes)) {
            unset($matchedRoutes);
            $this->action = $firstRoute['action'];
            $this->actionNamespace = $firstRoute['namespace'];
            $this->responderNamespace = $firstRoute['responder'];
            $this->params = $firstRoute['params'];

            $this->wasMatched = true;
        } else {
            $this->wasMatched = false;
        }
    }

    /**
     * Set uri source
     *
     * @param $uriSource
     */
    public function setUriSource($uriSource)
    {
        $this->uriSource = $uriSource;
    }

    /**
     * Get module
     *
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Checks if the router matches any route
     *
     * @return bool
     */
    public function wasMatched()
    {
        return $this->wasMatched;
    }

    /**
     * Returns the processed parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get action namespace
     *
     * @return string
     */
    public function getActionNamespace()
    {
        return $this->actionNamespace;
    }

    /**
     * Get responder namespace
     *
     * @return string
     */
    public function getResponderNamespace()
    {
        return $this->responderNamespace;
    }
}
