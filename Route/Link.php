<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Route;

/**
 * Lemmon PHP Framework core library.
 */
class Link
{
    private $_route;
    private $_scheme = 'http';
    private $_host;
    private $_link;
    private $_query = [];


    /**
     * Constructor.
     * @param  \Lemmon\Route $route
     * @param  string        $link
     * @param  array         $params
     * @return string
     */
    final function __construct(\Lemmon\Route $route, $link, $params = null)
    {
        // route
        $this->_route = $route;
        
        // arguments
        $args = array_slice(func_get_args(), 1);
        
        // params
        /*
        $params = (is_array($params)) ? $params : [];
        $params['!app'] = [
            'controller' => \Application::getController(),
            'action'     => \Application::getAction(),
        ];
        */
        
        // check for registered link
        if (is_string($link) and $link{0} == ':') {
            if (!($link = $route->getDefinedRoutes()[$link])) throw new \Exception(sprintf('Route %s not defined.', $link));
        }
        
        if (is_callable($link)) {
            $link = $link($this, array_slice($args, 1));
        }
        
        if (!is_string($link)) {
            throw new \Exception('Invalid route type.');
        }
        
        // match link variables with params
        preg_match_all('#@?((%(?<argument>\d+)|\$(\(?)((?<call>\w+::\w+)|(?<match>[\w\.]+))\)?))#', $link, $m, PREG_SET_ORDER);
        foreach ($m as $replace)
        {
            if ($_call = $replace['call'] and $_res = call_user_func($_call)) {
                $link = str_replace($replace[0], $_res, $link);
            }
            elseif ($_arg = (int)$replace['argument'] and $_arg = $args[$_arg] and (is_string($_arg) or is_numeric($_arg))) {
                $link = str_replace($replace[0], $_arg, $link);
            }
            elseif (is_array($params) and array_key_exists($replace['match'], $params)) {
                $link = str_replace($replace[0], $params[$replace['match']], $link);
            }
            elseif (is_object($params) and method_exists($params, $method = 'get' . $replace['match'])) {
                $link = str_replace($replace[0], $params->{$method}(), $link);
            }
            elseif (strpos($replace['match'], '.')) {
                $_replace = explode('.', $replace['match']);
                $_params = $params;
                foreach ($_replace as $_param) {
                    if (is_array($_params) and array_key_exists($_param, $_params)) {
                        $_params = $_params[$_param];
                    } elseif (is_object($_params) and isset($_params->{$_param})) {
                        $_params = $_params->{$_param};
                    } else {
                        $_params = false;
                        break;
                    }
                }
                if ($_params !== false) {
                    $link = str_replace($replace[0], $_params, $link);
                } else {
                    $link = str_replace($replace[1], '', $link);
                }
            }
            elseif (is_object($params) and !empty($params->{$replace['match']})) {
                $link = str_replace($replace[0], $params->{$replace['match']}, $link);
            }
            else {
                $link = str_replace($replace[1], '', $link);
            }
        }
        
        // asciize
        while (($i = strpos($link, '{')) !== false) {
            $j = strpos($link, '}', $i);
            $link = substr_replace($link, \Lemmon\String::asciize(substr($link, $i+1, $j-$i-1)), $i, $j-$i+1);
        }
        
        // parse url
        $link = $this->_parseUrl($link);
        
        // keep current values
        $link = explode('/', $link);
        foreach ($link as $key => $val) if ($val == '@') $link[$key] = $route->getParam($key + 1);
        $link = join('/', $link);
        $link = rtrim($link, '/');
        
        // res
        $this->_link = $link;
    }


    private function _parseUrl($url)
    {
        $url_parsed = parse_url($url);
        
        if ($url_parsed['scheme']) $this->_scheme = $url_parsed['scheme'];
        $this->_host = $url_parsed['host'];
        $this->_link = $link = $url_parsed['path'];
        if ($url_parsed['query']) {
            parse_str($url_parsed['query'], $query);
            $this->_query = $query;
        }
        return $link;
    }


    /**
     * Return link.
     * @return string
     */
    function __toString()
    {
        return ($this->_host ? $this->_scheme . '://' . $this->_host : '')
               // link
             . ($this->_link{0}=='/' ? '' : $this->_route->getRoot())
             . ($this->_link)
               // http query
             . ($this->_query ? '?' . http_build_query($this->_query) : '')
             ;
    }


    /**
     * Add return query.
     * @return self
     */
    function addReturnQuery()
    {
        $this->_query['redir'] = (string)$this->_route->getSelf()->includeQuery();
        return $this;
    }


    /**
     * Include host.
     * @return self
     */
    function includeHost($host = null)
    {
        if (isset($host)) {
            $this->_host = $host;
        } elseif (empty($this->_host)) {
            $this->_host = $this->_route->getHost();
        }
        return $this;
    }


    /**
     * Include query.
     * @return self
     */
    function includeQuery()
    {
        $this->_query = $_GET;
        return $this;
    }


    /**
     * Add query.
     * @param  array $query
     * @return self
     */
    function query(array $query)
    {
        $this->_query = $query;
        return $this;
    }
}