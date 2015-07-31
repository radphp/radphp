<?php

namespace Rad\Routing;

use Rad\Configure\Config;
use Rad\Core\Bundles;
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Network\Http\Request;
use Rad\Utility\Inflection;

/**
 * RadPHP Router
 *
 * @package Rad\Routing
 */
class Router implements ContainerAwareInterface
{
    protected $uriSource = self::URI_SOURCE_SERVER_REQUEST_URI;
    protected $bundle;
    protected $action;
    protected $actionNamespace;
    protected $responderNamespace;
    protected $params;
    protected $language;
    protected $isMatched = false;
    protected $container;
    protected $routingPhase;

    const ROUTING_PHASE_INDEX = 1;
    const ROUTING_PHASE_METHOD = 2;
    const ROUTING_PHASE_ACTION = 3;

    const DEFAULT_ACTION = 'Index';
    const URI_SOURCE_GET_URL = 'get_url_source';
    const URI_SOURCE_SERVER_REQUEST_URI = 'request_uri_source';

    const GEN_OPT_LANGUAGE = 'gen_opt_language';
    const GEN_OPT_WITH_PARAMS = 'gen_opt_with_params';
    const GEN_OPT_INC_DOMAIN = 'gen_opt_inc_domain';

    protected $generateDefaultOption = [
        self::GEN_OPT_LANGUAGE => true,
        self::GEN_OPT_WITH_PARAMS => true,
        self::GEN_OPT_INC_DOMAIN => true,
    ];

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
        if (PHP_SAPI === 'cli') {
            $method = 'cli';
        } else {
            /** @var Request $request */
            $request = $this->getContainer()->get('request');
            $method = $request->getMethod();
        }

        $method = ucfirst(strtolower($method)) . 'Method';

        if (!$uri) {
            $uri = $this->getRewriteUri();
        }

        $uri = trim($uri, '/');

        // remove empty cells
        $parts = [];
        foreach (explode('/', $uri) as $p) {
            if (trim($p) !== '') {
                $parts[] = $p;
            }
        }

        // check language
        if (in_array($parts[0], Config::get('languages.possible', ['en']))) {
            // We found the language, so set it as current language
            $this->language = array_shift($parts);
        } else {
            $this->language = Config::get('languages.default', 'en');
        }

        // Cleaning route parts & Rebase array keys
        $parts[] = strtolower(self::DEFAULT_ACTION);
        $camelizedParts = $parts;

        $camelizedParts = array_values(array_map('Rad\Utility\Inflection::camelize', $camelizedParts));
        $bundle = reset($camelizedParts);
        Inflection::camelize($bundle);
        $bundles = array_intersect([$bundle, 'App'], Bundles::getLoaded());

        $matchedRoute = null;
        foreach ($bundles as $bundleName) {
            // reset manipulation parameters
            $dummyCamelizedParts = $camelizedParts;
            $dummyParts = $parts;

            if ($bundleName === 'App' && $dummyCamelizedParts[0] != 'App') {
                array_unshift($dummyParts, $bundleName);
                array_unshift($dummyCamelizedParts, $bundleName);
            } else {
                // get bundle namespace instead of its name
                array_shift($dummyCamelizedParts);
                array_unshift($dummyCamelizedParts, trim(Bundles::getNamespace($bundleName), '\\'));
            }

            // add "Action" to array as second param
            array_splice($dummyCamelizedParts, 1, 0, 'Action');

            /**
             * routingPhase is sequence of three phases
             * 1- direct call of action
             * 2- direct call of method
             * 3- direct call of index action
             */
            $this->routingPhase = self::ROUTING_PHASE_INDEX;

            // Continue searching till you found any matching
            // Or you have at least three elements in array (Bundle, "Action", Action)
            for ($i = 0; count($dummyCamelizedParts) >= 3; $i++) {
                $actionNamespace = implode('\\', $dummyCamelizedParts) . 'Action';

                if (class_exists($actionNamespace)) {
                    array_splice($dummyCamelizedParts, 1, 1, 'Responder');
                    $responderNamespace =
                        implode('\\', $dummyCamelizedParts) . 'Responder';

                    $matchedRoute = [
                        'namespace' => $actionNamespace,
                        'responder' => $responderNamespace,
                        'action' => ($this->routingPhase == self::ROUTING_PHASE_METHOD)
                            ? $method
                            : $dummyParts[count($dummyCamelizedParts) - 2],
                        'bundle' => strtolower($bundleName),
                        'params' => array_slice($dummyParts, count($dummyCamelizedParts) - $this->routingPhase, -1)
                    ];

                    break 2;
                }

                array_pop($dummyCamelizedParts);

                // change router for some other default paths
                if ($this->routingPhase > self::ROUTING_PHASE_ACTION) {
                    $this->routingPhase = self::ROUTING_PHASE_INDEX;
                }

                if ($this->routingPhase == self::ROUTING_PHASE_METHOD) {
                    $dummyCamelizedParts[] = self::DEFAULT_ACTION;
                }

                if ($this->routingPhase == self::ROUTING_PHASE_INDEX) {
                    $dummyCamelizedParts[] = $method;
                }

                $this->routingPhase++;
            }
        }

        if ($matchedRoute) {
            $this->action = $matchedRoute['action'];
            $this->bundle = $matchedRoute['bundle'];
            $this->actionNamespace = $matchedRoute['namespace'];
            $this->responderNamespace = $matchedRoute['responder'];
            $this->params = $matchedRoute['params'];

            $this->isMatched = true;
        }
    }

    /**
     * Generate link base on bundles, with check for correct bundle and action
     * Possible options:
     *      Router::GEN_OPT_LANGUAGE to add language or not, default: true
     *      Router::GEN_OPT_WITH_PARAMS to add parameters or not, default: true
     *
     * @param array $url     An array to represent list of URL elements
     * @param array $options options to change generator behaviour
     *
     * @return string
     */
    public function generateUrl(
        $url = [],
        $options = [
            self::GEN_OPT_LANGUAGE => true,
            self::GEN_OPT_WITH_PARAMS => true,
            self::GEN_OPT_INC_DOMAIN => true
        ]
    ) {
        if (!is_array($url)) {
            $url = [];
        }

        $bundle = strtolower(isset($url[0]) ? array_shift($url) : $this->bundle);
        $action = strtolower(isset($url[0]) ? array_shift($url) : $this->action);

        $result = [$bundle];

        // set action only if it is in action routing mode
        if ($this->routingPhase == self::ROUTING_PHASE_ACTION) {
            $result[] = $action;
        }

        // add additional parameters
        if (isset($options[self::GEN_OPT_WITH_PARAMS])) {
            $addParams = $options[self::GEN_OPT_WITH_PARAMS];
        } else {
            $addParams = $this->generateDefaultOption[self::GEN_OPT_WITH_PARAMS];
        }

        if ($addParams) {
            $result = array_merge($result, $this->params);
        }

        // add language
        if (isset($options[self::GEN_OPT_LANGUAGE])) {
            $addLanguage = $options[self::GEN_OPT_LANGUAGE];
        } else {
            $addLanguage = $this->generateDefaultOption[self::GEN_OPT_LANGUAGE];
        }

        if ($addLanguage) {
            array_unshift($result, $this->language);
        }

        // include domain
        if (isset($options[self::GEN_OPT_INC_DOMAIN])) {
            $incDomain = $options[self::GEN_OPT_INC_DOMAIN];
        } else {
            $incDomain = $this->generateDefaultOption[self::GEN_OPT_INC_DOMAIN];
        }

        $result = '/' . implode('/', $result);

        if ($incDomain && $this->container->get('registry')->get('method') != 'cli') {
            /** @var Request $request */
            $request = $this->getContainer()->get('request');
            $result = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $result;
        }

        return $result;
    }

    /**
     * Change default behaviour of adding language to URL
     *
     * @param $bool
     */
    public function setGenerateUrlOptionLanguage($bool)
    {
        $this->generateDefaultOption[self::GEN_OPT_LANGUAGE] = $bool;
    }

    /**
     * Change default behaviour of adding parameters to the end of URL
     *
     * @param $bool
     */
    public function setGenerateUrlOptionParams($bool)
    {
        $this->generateDefaultOption[self::GEN_OPT_WITH_PARAMS] = $bool;
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
     * Get bundle
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
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
     * Get current language if supported, else return default
     *
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set container
     *
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
