<?php

namespace Rad\Routing;

use Rad\Config;

/**
 * Router
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
     */
    public function handle()
    {
        $rewriteUri = trim($this->getRewriteUri(), '/');
        $parts = explode('/', $rewriteUri);

        // Cleaning route parts & Rebase array keys
        $parts = array_values(array_filter($parts, 'trim'));

        // Assign module if exist
        if (array_key_exists($module = strtolower(reset($parts)), Config::get('bundles', []))) {
            $this->module = $module;
            array_shift($parts);
        }

        if ($this->module) {
            //TODO Implement bundles namespace
        } else {
            $appActionNS = 'App\\Action';
            $appResponderNS = 'App\\Responder';
        }

        $matchedRoutes = [];
        foreach ($parts as $key => $part) {
            $appActionNS .= '\\' . ucfirst($part);
            $appResponderNS .= '\\' . ucfirst($part);
            $namespace = $appActionNS . 'Action';
            $responderNS = $appResponderNS . 'Responder';

            if (class_exists($namespace)) {
                $matchedRoutes[] = [
                    'namespace' => $namespace,
                    'responder' => $responderNS,
                    'action' => $part,
                    'params' => array_slice($parts, $key + 1)
                ];
            }
        }

        if ($lastRoute = array_pop($matchedRoutes)) {
            $this->action = $lastRoute['action'];
            $this->actionNamespace = $lastRoute['namespace'];
            $this->responderNamespace = $lastRoute['responder'];
            $this->params = $lastRoute['params'];

            $this->wasMatched = true;
        } else {
            $this->wasMatched = false;
        }
    }

    public function setUriSource($uriSource)
    {
        $this->uriSource = $uriSource;
    }

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
