<?php

namespace Rad\Routing;

use Exception;
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
    protected $isMatched = false;

    const DEFAULT_ACTION = 'index';
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
        $camelizedParts = $parts;
        // I really need to add index to both of them separately! Because of "lazy copy" feature of PHP
        $camelizedParts[] = self::DEFAULT_ACTION;
        $parts[] = self::DEFAULT_ACTION;

        array_values(array_filter($camelizedParts, [$this, 'camelize']));
        $module = reset($parts);
        $this->camelize($module);
        $bundles = array_intersect(['App', $module], Bundles::getLoaded());

        $matchedRoute = null;
        foreach ($bundles as $bundleName) {
            // reset manipulation parameters
            $dummyCamelizedParts = $camelizedParts;
            $dummyParts = $parts;

            if ($bundleName === 'App') {
                array_unshift($dummyParts, $bundleName);
                array_unshift($dummyCamelizedParts, $bundleName);
            }

            // add "Action" to array as second param
            array_splice($dummyCamelizedParts, 1, 0, 'Action');

            // Continue searching till you found any matching
            // Or you have at least three elements in array (Bundle, "Action", Action)
            for ($i = 0; count($dummyCamelizedParts) >= 3; $i++) {
                $bundleNamespace['action'] = implode('\\', $dummyCamelizedParts);
                $bundleNamespace['responder'] = implode('\\', $dummyCamelizedParts);
                $actionNamespace = $bundleNamespace['action'] . 'Action';
                $responderNamespace = $bundleNamespace['responder'] . 'Responder';

                if (class_exists($actionNamespace)) {
                    $matchedRoute = [
                        'namespace' => $actionNamespace,
                        'responder' => $responderNamespace,
                        'action' => $dummyParts[count($dummyCamelizedParts) - 2],
                        'params' => array_slice($dummyParts, count($dummyCamelizedParts) - 1, -1)
                    ];

                    break 2;
                }

                array_pop($dummyCamelizedParts);

                // add index one in two iterates
                if ($i % 2) {
                    $dummyCamelizedParts[] = self::DEFAULT_ACTION;
                }
            }
        }

        if ($matchedRoute) {
            $this->action = $matchedRoute['action'];
            $this->actionNamespace = $matchedRoute['namespace'];
            $this->responderNamespace = $matchedRoute['responder'];
            $this->params = $matchedRoute['params'];

            $this->isMatched = true;
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
    public function isMatched()
    {
        return $this->isMatched;
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

    /**
     * Camelize underscorized strings
     *
     * @param $string
     */
    private function camelize(&$string)
    {
        $string = str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', trim($string)))));
    }
}
