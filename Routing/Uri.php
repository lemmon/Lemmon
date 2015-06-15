<?php

namespace Lemmon\Routing;

class Uri
{
    private $_router;
    private $_uri;


    public function __construct(AbstractRouter $router, $uri)
    {
        $this->_router = $router;
        $this->_uri = is_string($uri) ? parse_url($uri) : $uri;
        if (isset($this->_uri['query'])) {
            parse_str($this->_uri['query'], $this->_uri['query']); 
        }
    }


    public function query(array $query)
    {
        $this->_uri['query'] = isset($this->_uri['query']) ? array_merge($this->_uri['query'], $query) : $query;
        return $this;
    }


    public function includeHost($host = NULL, $scheme = NULL)
    {
        // host
        if (!$host) {
            $this->_uri['host'] = $host ?: $this->_router->getHost();
        }
        // scheme
        if (FALSE !== $scheme) {
            $this->_uri['scheme'] = $scheme ?: $this->_router->getScheme();
        }
        //
        return $this;
    }


    public function includeCurrentQuery()
    {
        $this->_uri['query'] = isset($this->_uri['query']) ? array_merge($this->_uri['query'], $this->_router->getQuery()) : $this->_router->getQuery();
        return $this;
    }


    public function __toString()
    {
        $uri = $this->_uri;
        $res = '';
        if (isset($uri['scheme'])) $res .= $uri['scheme'] . ':';
        if (isset($uri['host'])) $res .= '//' . $uri['host'];
        if (isset($uri['path'])) $res .= $uri['path'];
        if (isset($uri['query'])) $res .= '?' . urldecode(http_build_query($uri['query']));
        if (isset($uri['fragment'])) $res .= '#' . $uri['fragment'];
        return $res;
        // user
        // pass
        // query
    }
}