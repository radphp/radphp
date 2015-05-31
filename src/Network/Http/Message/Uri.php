<?php

namespace Rad\Network\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Http Message Uri
 *
 * @package Rad\Network\Http\Message
 */
class Uri implements UriInterface
{
    protected $scheme = '';
    protected $userInfo = '';
    protected $host = '';
    protected $port;
    protected $path = '';
    protected $query = '';
    protected $fragment = '';

    const CHARACTERS_UNRESERVED = 'a-zA-Z0-9_\-\.~';
    const CHARACTERS_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /**
     * Rad\Network\Http\Message\Uri constructor
     *
     * @param string $url Url
     */
    public function __construct($url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $parsedUrl = parse_url($url);

        if (false === filter_var($url, FILTER_VALIDATE_URL) || false === $parsedUrl) {
            throw new InvalidArgumentException(sprintf('URL "%s" seriously malformed.', $url));
        }

        $parsedUrl = $parsedUrl + [
                'scheme' => '',
                'host' => '',
                'port' => null,
                'user' => '',
                'pass' => '',
                'path' => '',
                'fragment' => '',
                'query' => ''
            ];

        $this->scheme = $this->filterScheme($parsedUrl['scheme']);
        $this->host = $this->filterHost($parsedUrl['host']);
        $this->port = $this->filterPort($parsedUrl['port']);
        $this->path = $this->filterPath($parsedUrl['path']);
        $this->query = $this->filterQuery($parsedUrl['query']);
        $this->fragment = $this->filterFragment($parsedUrl['fragment']);

        if ($parsedUrl['user']) {
            if ($parsedUrl['pass']) {
                $this->userInfo = $parsedUrl['user'] . ':' . $parsedUrl['pass'];
            } else {
                $this->userInfo = $parsedUrl['user'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $authority = '';

        $authority .= !empty($this->userInfo) ? $this->userInfo . '@' : '';
        $authority .= !empty($this->host) ? $this->host : '';
        $authority .= !empty($this->port) ? ':' . $this->port : '';

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $newInstance = clone $this;
        $newInstance->scheme = $this->filterScheme($scheme);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $newInstance = clone $this;

        if ($user) {
            if ($password) {
                $newInstance->userInfo = $user . ':' . $password;
            } else {
                $newInstance->userInfo = $user;
            }
        } else {
            $newInstance->userInfo = '';
        }

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $newInstance = clone $this;
        $newInstance->host = $this->filterHost($host);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $newInstance = clone $this;
        $newInstance->port = $this->filterPort($port);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $newInstance = clone $this;
        $newInstance->path = $this->filterPath($path);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $newInstance = clone $this;
        $newInstance->query = $this->filterQuery($query);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $newInstance = clone $this;
        $newInstance->fragment = $this->filterFragment($fragment);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $url = '';

        $url .= $this->scheme ? $this->scheme . ':' : '';
        $url .= $this->getAuthority() ? '//' . $this->getAuthority() : '';

        if (strpos($this->path, '/') !== 0 && $this->getAuthority()) {
            $url .= '/' . $this->path;
        } elseif (preg_match("/\/\//i", $this->path) && !$this->getAuthority()) {
            $url .= '/' . ltrim($this->path, '/');
        } else {
            $url .= $this->path;
        }

        $url .= $this->query ? '?' . $this->query : '';
        $url .= $this->fragment ? '#' . $this->fragment : '';

        return $url;
    }

    /**
     * Filter scheme
     *
     * @param string $scheme
     *
     * @return string
     */
    protected function filterScheme($scheme)
    {
        $scheme = rtrim(strtolower($scheme), ':/');

        if (!preg_match('/^[a-z][a-z0-9.+-]*$/i', $scheme)) {
            throw new InvalidArgumentException('Scheme "%s" invalid or unsupported.', $scheme);
        }

        return $scheme;
    }

    /**
     * Filter host
     *
     * @param string $host
     *
     * @return string
     */
    protected function filterHost($host)
    {
        if ($host) {
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                $host = strval($host);
            } elseif (preg_match(
                '/^(?:[' . self::CHARACTERS_UNRESERVED . self::CHARACTERS_SUB_DELIMS . ':@\/\?]+|%[A-Fa-f0-9]{2})+$/',
                $host)
            ) {
                $host = strval($host);
            } else {
                throw new InvalidArgumentException('Invalid host "%s".', $host);
            }
        } else {
            $host = '';
        }

        return $host;
    }

    /**
     * Filter port
     *
     * @param int|null $port
     *
     * @return int|null
     */
    protected function filterPort($port)
    {
        if ($port !== null) {
            $port = intval($port);

            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException(sprintf('Invalid port "%s".', $port));
            }
        } else {
            $port = null;
        }

        return $port;
    }

    /**
     * Filter path
     *
     * @param string $path
     *
     * @return string
     */
    protected function filterPath($path)
    {
        $path = preg_replace_callback(
            '/(?:[^' . self::CHARACTERS_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );

        if (is_null($path)) {
            throw new InvalidArgumentException(sprintf('Invalid path "%s".', $path));
        }

        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        return $path;
    }

    /**
     * Filter query
     *
     * @param string $query
     *
     * @return mixed|string
     */
    protected function filterQuery($query)
    {
        $query = preg_replace_callback(
            '/(?:[^' . self::CHARACTERS_UNRESERVED . self::CHARACTERS_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );

        if (is_null($query)) {
            throw new InvalidArgumentException(sprintf('Invalid query strings "%s".', $query));
        } elseif (!$query) {
            $query = '';
        }

        if (substr($query, 0, 1) === '?') {
            $query = substr($query, 1);
        }

        return $query;
    }

    /**
     * Filter fragment
     *
     * @param string $fragment
     *
     * @return string
     */
    protected function filterFragment($fragment)
    {
        $fragment = preg_replace_callback(
            '/(?:[^' . self::CHARACTERS_UNRESERVED . self::CHARACTERS_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $fragment
        );

        if (is_null($fragment)) {
            throw new InvalidArgumentException(sprintf('Invalid fragment "%s".', $fragment));
        } elseif (!$fragment) {
            $fragment = '';
        }

        if (substr($fragment, 0, 1) === '#') {
            $fragment = substr($fragment, 1);
        }

        return $fragment;
    }
}
