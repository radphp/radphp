<?php

namespace Rad\Routing;

use Rad\Configure\Config;
use Rad\Core\Bundles;
use Rad\DependencyInjection\Container;
use Rad\DependencyInjection\ContainerAwareInterface;
use Rad\Network\Http\Request;

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
    protected $prefix = [];

    /**
     * @var array List of REST prefixes to manipulate requests by REST standard
     */
    protected $restPrefixes = ['_'];

    const ROUTING_PHASE_INDEX = 1;
    const ROUTING_PHASE_METHOD = 2;
    const ROUTING_PHASE_ACTION = 3;

    const DEFAULT_ACTION = 'Index';
    const URI_SOURCE_GET_URL = 'get_url_source';
    const URI_SOURCE_SERVER_REQUEST_URI = 'request_uri_source';

    const GEN_OPT_LANGUAGE = 'gen_opt_language';
    const GEN_OPT_WITH_PARAMS = 'gen_opt_with_params';
    const GEN_OPT_INC_DOMAIN = 'gen_opt_inc_domain';
    const GEN_OPT_IS_REST = 'gen_opt_is_rest';

    protected $generateDefaultOption = [
        self::GEN_OPT_LANGUAGE => true,
        self::GEN_OPT_WITH_PARAMS => false,
        self::GEN_OPT_INC_DOMAIN => true,
        self::GEN_OPT_IS_REST => false
    ];

    /**
     * Get rewrite info. This info is read from $_GET['_url'].
     * This returns '/' if the rewrite information cannot be read
     *
     * @return string
     */
    protected function getRewriteUri()
    {
        if ($this->uriSource === self::URI_SOURCE_SERVER_REQUEST_URI) {
            if (isset($_SERVER['REQUEST_URI'])) {
                $requestUri = explode('?', $_SERVER['REQUEST_URI']);
                if (!empty($requestUri[0])) {
                    return $requestUri[0];
                }
            }
        } else {
            if (isset($_GET['_url']) && !empty($_GET['_url'])) {
                return $_GET['_url'];
            }
        }

        return '/';
    }

    /**
     * Handles routing information received from the rewrite engine
     *
     * @param string|null $uri
     */
    public function handle($uri = null)
    {
        $method = $this->prepareMethodName();

        if (empty($uri)) {
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

        $this->extractLanguage($parts);

        // check if it is REST or not, then do what is required
        $this->prepareRestRequest($parts);

        // Cleaning route parts & Rebase array keys
        $parts[] = strtolower(self::DEFAULT_ACTION);
        $camelizedParts = $parts;

        $camelizedParts = array_values(array_map('Rad\Utility\Inflection::camelize', $camelizedParts));
        $bundle = reset($camelizedParts);
        $bundles = array_intersect([$bundle, 'App'], Bundles::getLoaded());

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

            $this->routingPhase = self::ROUTING_PHASE_ACTION;
            /**
             * routingPhase is sequence of three phases, in the following order
             * 1- direct call of action
             * 2- direct call of method
             * 3- direct call of index action
             */

            // Continue searching till you found any matching
            // Or you have at least three elements in array (Bundle, "Action", Action)
            while (count($dummyCamelizedParts) >= 3) {
                $actionNamespace = implode('\\', $dummyCamelizedParts) . 'Action';

                if (class_exists($actionNamespace)) {
                    $this->finalizeRouterArguments(
                        $dummyParts,
                        $dummyCamelizedParts,
                        $actionNamespace,
                        $bundleName,
                        $method
                    );

                    break 2;
                }

                array_pop($dummyCamelizedParts);

                // change router for some other default paths
                switch ($this->routingPhase) {
                    case self::ROUTING_PHASE_INDEX:
                        $this->routingPhase = self::ROUTING_PHASE_ACTION;
                        break;
                    case self::ROUTING_PHASE_METHOD:
                        $dummyCamelizedParts[] = self::DEFAULT_ACTION;
                        $this->routingPhase--;
                        break;
                    case self::ROUTING_PHASE_ACTION:
                        $dummyCamelizedParts[] = $method;
                        $this->routingPhase--;
                        break;
                }
            }
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
            self::GEN_OPT_WITH_PARAMS => false,
            self::GEN_OPT_INC_DOMAIN => true,
            self::GEN_OPT_IS_REST => false
        ]
    ) {
        $result = [];
        if (!is_array($url)) {
            $url = [$url];
        }

        if (empty($url)) {
            $result = [$this->bundle];

            // set action only if it is in action routing mode
            if ($this->routingPhase == self::ROUTING_PHASE_ACTION) {
                $result[] = $this->action;
            }
        }

        // add additional parameters
        $result = array_merge($result, $url);

        // add additional parameters
        $this->genAddParameters($options, $result);

        $result = array_merge($this->prefix, $result);

        // add rest prefix
        $this->genRestPrefix($options, $result);

        // add language
        $this->genAddLanguage($options, $result);

        $result = '/' . implode('/', $result);
        $result = preg_replace('#/+#', '/', $result);

        // include domain
        $this->genAddDomain($options, $result);

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

    /**
     * Set prefixe for router to prepend to generated URL
     *
     * @param array $prefix
     *
     * @return Router
     */
    public function setPrefix(array $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get prefix array
     *
     * @return array
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return array
     */
    public function getRestPrefixes()
    {
        return $this->restPrefixes;
    }

    /**
     * @param string|string[] $restPrefixes
     */
    public function setRestPrefixes($restPrefixes)
    {
        if (!is_array($restPrefixes)) {
            $restPrefixes = [$restPrefixes];
        }

        $this->checkRestPrefixValidity($restPrefixes);

        $this->restPrefixes = $restPrefixes;
    }

    /**
     * @param string|string[] $restPrefixes
     */
    public function appendRestPrefixes($restPrefixes)
    {
        if (!is_array($restPrefixes)) {
            $restPrefixes = [$restPrefixes];
        }

        $this->checkRestPrefixValidity($restPrefixes);

        $this->restPrefixes = array_unique(array_merge($restPrefixes, $this->restPrefixes));
    }

    /**
     * Check if REST prefixes are valid or not. It will throw exception on error
     *
     * @param array $restPrefixes REST prefixes
     * @throws DomainException
     */
    private function checkRestPrefixValidity(array $restPrefixes)
    {
        foreach ($restPrefixes as $prefix) {
            if (!is_string($prefix)) {
                throw new DomainException('All provided prefixes for REST must be string');
            }

            // if it has "/" in it
            if (strpos('/', $prefix)) {
                throw new DomainException('Prefixes must not contain "/"');
            }
        }
    }

    /**
     * Prepare method name, choose between CLI and all other HTTP methods
     *
     * @return string Method name + "Method"
     */
    protected function prepareMethodName()
    {
        if (PHP_SAPI === 'cli') {
            $method = 'cli';
        } else {
            /** @var Request $request */
            $request = $this->getContainer()->get('request');
            $method = $request->getMethod();
        }

        return ucfirst(strtolower($method)) . 'Method';
    }

    /**
     * If route is prefixed with language, extract it
     *
     * @param array $parts router parts
     */
    protected function extractLanguage(array &$parts)
    {
        if (!empty($parts) && in_array($parts[0], Config::get('languages.possible', ['en']))) {
            // We found the language, so set it as current language
            $this->language = array_shift($parts);
        } else {
            $this->language = Config::get('languages.default', 'en');
        }
    }

    /**
     * Prepare REST requests
     *
     * @param array $parts router parts
     */
    protected function prepareRestRequest(array &$parts)
    {
        if (!empty($parts) && in_array($parts[0], $this->restPrefixes)) {
            /*
             * if the request is something like /_/posts/1/comments/2
             * it will convert it to something like /posts/comments/1/2
             */

            // first remove the REST prefix
            array_shift($parts);

            $restRoute = $restParams = [];
            $i = 1;

            foreach ($parts as $part) {
                if ($i % 2) {
                    $restRoute[] = $part;
                } else {
                    $restParams[] = $part;
                }

                $i++;
            }

            $parts = array_merge($restRoute, $restParams);
        }
    }

    /**
     * Finalize and set all route parameters
     *
     * @param array  $parts           Router parts
     * @param array  $camelizedParts  Camelized router parts
     * @param string $actionNamespace Namespace
     * @param string $bundleName      Bundle
     * @param string $method          Method
     */
    private function finalizeRouterArguments(
        array $parts,
        array $camelizedParts,
        $actionNamespace,
        $bundleName,
        $method
    ) {
        array_splice($camelizedParts, 1, 1, 'Responder');
        $responderNamespace = implode('\\', $camelizedParts) . 'Responder';

        $delta = ($this->routingPhase == self::ROUTING_PHASE_METHOD) ? 2 : 1;

        $this->action = ($this->routingPhase == self::ROUTING_PHASE_METHOD)
            ? $method
            : $parts[count($camelizedParts) - 2];
        $this->bundle = strtolower($bundleName);
        $this->actionNamespace = $actionNamespace;
        $this->responderNamespace = $responderNamespace;
        $this->params = array_slice($parts, count($camelizedParts) - $delta, -1);

        $this->isMatched = true;
    }

    /**
     * @param $options
     * @param $result
     */
    private function genAddParameters(&$options, &$result)
    {
        $addParams = isset($options[self::GEN_OPT_WITH_PARAMS]) ?
            $options[self::GEN_OPT_WITH_PARAMS] :
            $this->generateDefaultOption[self::GEN_OPT_WITH_PARAMS];

        if ($addParams) {
            $result = array_merge($result, $this->params);
        }
    }

    /**
     * @param $options
     * @param $result
     */
    private function genRestPrefix(&$options, &$result)
    {
        $isRest = isset($options[self::GEN_OPT_IS_REST]) ?
            $options[self::GEN_OPT_IS_REST] :
            $this->generateDefaultOption[self::GEN_OPT_IS_REST];

        if ($isRest && isset($this->restPrefixes[0])) {
            array_unshift($result, $this->restPrefixes[0]);
        }
    }

    /**
     * @param $options
     * @param $result
     */
    private function genAddLanguage(&$options, &$result)
    {
        $addLanguage = isset($options[self::GEN_OPT_LANGUAGE]) ?
            $options[self::GEN_OPT_LANGUAGE] :
            $this->generateDefaultOption[self::GEN_OPT_LANGUAGE];

        if ($addLanguage) {
            array_unshift($result, $this->language);
        }
    }

    /**
     * @param $options
     * @param $result
     */
    private function genAddDomain($options, &$result)
    {
        $incDomain = isset($options[self::GEN_OPT_INC_DOMAIN]) ?
            $options[self::GEN_OPT_INC_DOMAIN] :
            $this->generateDefaultOption[self::GEN_OPT_INC_DOMAIN];

        if ($incDomain && 'cli' !== PHP_SAPI) {
            /** @var Request $request */
            $request = $this->getContainer()->get('request');
            $result = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $result;
        }
    }
}
