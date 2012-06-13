<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
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
	private $_linkRaw;
	private $_query = array();


	/**
	 * Constructor.
	 * @param  \Lemmon\Route $route
	 * @param  string        $link
	 * @param  array         $params
	 * @return string
	 */
	final function __construct(\Lemmon\Route $route, $link, $params=null)
	{
		// route
		$this->_route = $route;
		
		// url
		$link = $this->_parseUrl($this->_linkRaw=(string)$link);

		// check for registered link
		if ($link{0}==':') 
		{
			if (!($link=$route->getDefinedRoutes()[$link])) throw new \Exception(sprintf('Route `%s` not defined', $this->_linkRaw));
		}
		
		// match link variables with params
		preg_match_all('/@?(\$([\w\.]+))/i', $link, $m,  PREG_SET_ORDER);
		foreach ($m as $replace)
		{
			if (is_array($params) and array_key_exists($replace[2], $params))
			{
				$link = str_replace($replace[0], $params[$replace[2]], $link);
			}
			elseif (is_object($params) and method_exists($params, $method='get'.$replace[2]))
			{
				$link = str_replace($replace[0], $params->{$method}(), $link);
			}
			elseif (strpos($replace[2], '.'))
			{
				$_replace = explode('.', $replace[2]);
				if (count($_replace)==2)
					$link = str_replace($replace[0], $params->{$_replace[0]}->{$_replace[1]}, $link);
				elseif (count($_replace)==3)
					$link = str_replace($replace[0], $params->{$_replace[0]}->{$_replace[1]}->{$_replace[2]}, $link);
			}
			elseif (is_object($params) and isset($params->{$replace[2]}))
			{
				$link = str_replace($replace[0], $params->{$replace[2]}, $link);
			}
			else
			{
				$link = str_replace($replace[1], '', $link);
			}
		}
		
		// asciize
		while (($i=strpos($link, '{'))!==false)
		{
			$j = strpos($link, '}', $i);
			$link = substr_replace($link, \Lemmon\String::asciize(substr($link, $i+1, $j-$i-1)), $i, $j-$i+1);
		}
		
		// keep current values
		$link = explode('/', $link);
		foreach ($link as $key => $val) if ($val=='@') $link[$key] = $route->getParam($key+1);
		$link = join('/', $link);
		$link = rtrim($link, '/');
		
		// res
		$this->_link = $link;
	}


	private function _parseUrl($url)
	{
		$url_parsed = parse_url($url);
		if ($url_parsed) $this->_scheme = $url_parsed['scheme'];
		$this->_host = $url_parsed['host'];
		$this->_link = $link = $url_parsed['path'];
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
		$this->_query['redir'] = (string)$this->_route->getSelf();
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