<?php
/**
* This file is part of the Lemmon Framework.
*
* Copyright 2007-2010, Jakub Pelák (http://www.lemmonjuice.com)
*/

/**
 * Handles routing and other miscellaneous functions with links.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_Route
{
	private static $_instance;
	
	private $_host;
	private $_root;
	private $_route;
	private $_params = array();
	private $_matches = array();
	private $_definedRoutes = array();
	
	private $_controller = 'index';
	private $_action = 'index';
	
	private $_uploadDir;
	private $_uploadURL;


	/**
	 * Build a Route Controller.
	 *
	 * Defines all the necessary parameters required to run this class
	 * properly.
	 */
	final function __construct()
	{
		self::$_instance = $this;
		
		$this->_host = $_SERVER['HTTP_HOST'];
		$this->_root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

		if (isset($_GET['request_uri']))
		{
			$route = $_GET['request_uri'];
		}
		else
		{
			$route = substr( ($i=strpos($_SERVER['REQUEST_URI'], '?')) ? substr($_SERVER['REQUEST_URI'], 0, $i) : $_SERVER['REQUEST_URI'] , strlen($this->_root));
		}

		if ($this->_route=$route)
		{
			$this->_params = explode('/', $route);
		}
		
		$this->_uploadDir = ROOT_DIR . '/' . 'uploads/';
		$this->_uploadURL = $this->_root . 'uploads/';
		
		$this->define();
	}


	/**
	 * Helper function used to define application specific parameters.
	 */
	protected function define() {}


	/**
	 * Get current defined instance.
	 * @return Lemmon_Route
	 */
	final static function getInstance()
	{
		return self::$_instance;
	}


	/**
	 * Returns matched parameter of current route.
	 * @param  string
	 * @return mixed  either string on defined match or null on undefined one
	 */
	final function __get($key)
	{
		return array_key_exists($key, $this->_matches) ? $this->_matches[$key] : null;
	}


	final function __set($key, $val)
	{
		$this->_matches[$key] = $val;
	}


	/**
	 * Get current host.
	 * @return string
	 */
	final static function getHost()
	{
		return $_SERVER['HTTP_HOST'];
	}


	/**
	 * Get current domain.
	 * @return string
	 */
	final static function getDomain()
	{
		$domain = self::getHost();
		if (substr_count($domain, '.')>1)
		{
			$domain = substr($domain, strpos($domain, '.') + 1);
		}
		return $domain;
	}


	/**
	 * Get current subdomain.
	 * @return string
	 */
	final static function getSubdomain()
	{
		$subdomain = self::getHost();
		if (substr_count($subdomain, '.')>1)
		{
			$subdomain = substr($subdomain, 0, strpos($subdomain, '.'));
		}
		return $subdomain;
	}


	/**
	 * Get absolute link for root.
	 * @return string
	 */
	final function getRoot()
	{
		return $this->_root;
	}


	/**
	 * Get route.
	 * @return string
	 */
	final function getRoute()
	{
		return $this->_route;
	}


	/**
	 * Get absolute link for current route.
	 * @return string
	 */
	final function getSelf()
	{
		return $this->_root . $this->_route;
	}


	/**
	 * Get absolute link for current route.
	 * @param  boolean
	 * @return string
	 */
	final function getSelfWithParams()
	{
		$res = self::getSelf();
		if ($_GET) $res .= '?' . http_build_query( $_GET, '', '&' );
		return $res;
	}


	/**
	 * Get all route parameters.
	 * @return array
	 */
	final function getParams()
	{
		return $this->_params;
	}


	/**
	 * Get parameter of current route.
	 * @param  mixed
	 * @return string
	 */
	final function getParam($i, $default=null)
	{
		if (strpos($i, '/'))
		{
			$i = explode('/', $i);
			$default = explode('/', $default);
			foreach ($i as $key => $val) $i[$key] = self::getParam($key+1, $default[$key], true);
			return join('/', $i);
		}
		elseif (is_numeric($i))
		{
			return ($res=$this->_params[$i-1]) ? $res : $default;
		}
		else
		{
			return $this->{$i};
		}
	}


	final function setParam($i, $val)
	{
		$this->_params[$i-1] = $val;
	}


	/**
	 * Get Controller name.
	 * @return string
	 */
	final function getController()
	{
		return $this->_controller;
	}


	/**
	 * Get Action name.
	 * @return string
	 */
	final function getAction()
	{
		return $this->_action;
	}


	/**
	 * Checks if the request is XMLHttpRequest (XHR).
	 * @return boolean
	 */
	final static function isXHR()
	{
		return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest' ? true : false;
	}


	/**
	 * Get absolute link to a directory.
	 * @param  string
	 * @return string
	 */
	function getPublic($link)
	{
		return $this->_root . 'public/' . $link;
	}


	/**
	 * Get absolute link to a upload directory.
	 * @return string
	 */
	final function getUploadDir()
	{
		return $this->_uploadDir;
	}


	final function setUploadDir($dir)
	{
		try
		{
			if (!file_exists($dir)) mkdir($dir, 0777, 1);
			$this->_uploadDir = $dir;
		}
		catch (Exception $e)
		{
			throw new Lemmon_Exception('Upload directory not writable');
		}
	}


	final function setUploadURL($url)
	{
		$this->_uploadURL = $url;
	}


	final function getUploadURL()
	{
		return $this->_uploadURL;
	}


	/**
	 * Set Controller name.
	 * @param  string
	 * @return string
	 */
	final function setController($controller)
	{
		$this->_controller = $controller;
		return $this->_controller;
	}


	/**
	 * Set Action name.
	 * @param  string
	 * @return string
	 */
	final function setAction($action)
	{
		$this->_action = str_replace(array('-', '.'), '_', $action);
		return $this;
	}


	/**
	 * Match current host with specific regular expression.
	 * @param  string case insensitive regex pattern
	 * @return boolean
	 */
	final static function matchHost($pattern)
	{
		$pattern = str_replace('.', '\.', $pattern);
		return (bool)preg_match('/' . $pattern . '/i', self::getHost());
	}


	/**
	 * Match current route with specific regular expression.
	 *
	 * This function matches current route with provided case insensitive
	 * regular expressison including specific conditions. Also, automatically
	 * defines Controller and Action if passed in the pattern. Slashes should
	 * not be escaped.
	 *
	 * `$this->match('$controller(/$action)', array('controller'=>'[\w\-]+', 'action'=>'[\w\-]+'));`
	 *
	 * @param  string  case insensitive regex pattern
	 * @param  array   array of case insensitive regex pattern bits
	 * @return boolean
	 */
	final function match($pattern, $conditions=array())
	{
		$pattern = str_replace('/', '\/', $pattern);
		$pattern = str_replace('.', '\.', $pattern);
		$pattern = '^' . str_replace(')', ')?', $pattern);
		
		if (is_array($conditions)) foreach ($conditions as $key => $val)
		{
			$pattern = str_replace('$' . $key, '(?P<' . $key . '>' . $val . ')', $pattern);
		}
		
		if (preg_match('/'.$pattern.'/i', $this->_route, $matches))
		{
			$this->_matches = $matches;
			if ($matches['controller']) $this->_controller = $matches['controller'];
			if ($matches['action']) $this->setAction($matches['action']);
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Register shortcut for a custom route.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	final function register($key, $link)
	{
		$this->_definedRoutes[':' . $key] = $link;
	}


	/**
	 * Parse link.
	 * @param  string
	 * @param  array
	 * @return string
	 */
	final function parse($link, $params)
	{
		preg_match_all('/@?(\$([\w]+))/i', $link, $m,  PREG_SET_ORDER);
	
		foreach ($m as $replace)
		{
			if (isset($params[$replace[2]]))
			{
				$val = $params[$replace[2]];
				$val = strtolower($val);
				$val = trim($val, '-');
				$link = str_replace($replace[0], $val, $link);
			}
			else
			{
				$link = str_replace($replace[1], '', $link);
			}
		}
		
		while (($i=strpos($link, '{'))!==false)
		{
			$j = strpos($link, '}', $i);
			$link = substr_replace($link, Lemmon\String::asciize(substr($link, $i+1, $j-$i-1)), $i, $j-$i+1);
		}
		
		return $link;
	}


	/**
	 * Get an absolute route valid for current application.
	 * @param  string
	 * @param  array
	 * @return string
	 */
	final function to($link=null, $params=null)
	{
		if ($link{0}==':') 
		{
			if (!($link=$this->_definedRoutes[$link])) return false;
		}
		
		if (substr($link, 0, 9)=='~uploads/')
		{
			$link = str_replace('~uploads/', $this->getUploadURL(), $link);
		}
		
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
		
		while (($i=strpos($link, '{'))!==false)
		{
			$j = strpos($link, '}', $i);
			$link = substr_replace($link, Lemmon\String::asciize(substr($link, $i+1, $j-$i-1)), $i, $j-$i+1);
		}
		
		$link = explode('/', $link);
		foreach ($link as $key => $val) if ($val=='@') $link[$key] = $this->_params[$key];
		$link = join('/', $link);
		$link = rtrim($link, '/');
		
		$res = ($link{0}=='/' ? '' : $this->_root) . $link;
		
		return $res;
	}


	/**
	 * @param  string
	 * @param  array
	 * @return string
	 */
	final function toParams($link='', $params=array())
	{
		$res = self::to($link, $params);
		if ($_GET) $res .= '?' . http_build_query($_GET, '', '&amp;');
		return $res;
	}


	/**
	 * @param  string
	 * @param  array
	 * @return string
	 */
	final function toReturn($link='', $params=array())
	{
		$res = self::to($link, $params);
		$q['redir'] = html_entity_decode(self::getSelfWithParams());
		$res .= '?' . http_build_query($q, '', '&amp;');
		return $res;
	}


	/**
	 * @param  string
	 * @param  array
	 * @return string
	 */
	final function toReturnSoft($link='', $params=array())
	{
		$res = self::to($link, $params);
		$q['return'] = html_entity_decode(self::getSelfWithParams());
		$res .= '?' . http_build_query($q, '', '&amp;');
		return $res;
	}


	/**
	 * @param  string
	 * @param  array
	 * @return string
	 */
	final function toBack($link='', $params=array())
	{
		if ($_GET['return'])
		{
			return $_GET['return'];
		}
		elseif ($_GET['redir'])
		{
			return $_GET['redir'];
		}
		else
		{
			return self::to($link, $params);
		}
	}


	/**
	 * @param  array
	 * @param  boolean
	 * @param  boolean
	 * @param  boolean
	 * @return string
	 */
	final function addParams($params, $with_return=false, $soft_return=false, $raw=false)
	{
		$params = array_merge($_GET, $params);
		if ($with_return) $params[ $soft_return ? 'return' : 'redir' ] = self::getSelfWithParams(true);
		return ($query=http_build_query( $params, '', $raw ? '&' : '&amp;' )) ? '?' . $query : '';
	}


	/**
	 * @param  array
	 * @param  boolean
	 * @param  boolean
	 * @param  boolean
	 * @return string
	 */
	final function withParams($params, $with_return=false, $soft_return=false)
	{
		if ($with_return) $params[ $soft_return ? 'return' : 'redir' ] = self::getSelfWithParams(true);
		return ($query=http_build_query( $params, '', '&' )) ? '?' . $query : '';
	}
}
