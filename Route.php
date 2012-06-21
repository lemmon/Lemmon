<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon;

/**
 * Handles routing and other miscellaneous functions with links.
 */
class Route
{
	private static $_instance;
	
	private $_urlParsed;
	private $_root;
	private $_route;
	private $_params = array();
	private $_matches = array();
	private $_definedRoutes = array();

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
		// current instance
		self::$_instance = $this;
		
		// get root
		$this->_root = $root = substr($_SERVER['SCRIPT_NAME'], 0, -strlen(basename($_SERVER['SCRIPT_NAME'])));
		
		// parse url
		$this->_urlParsed = $url_parsed = parse_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		
		// get route
		$this->_route = $route = substr($url_parsed['path'], strlen($root));
		
		// process route
		if ($route)
		{
			$this->_params = explode('/', $route);
		}
		
		// default upload dirs
		$this->_uploadDir = ROOT_DIR . '/' . 'uploads/';
		$this->_uploadURL = $this->_root . 'uploads/';
		
		// init class
		if (method_exists($this, '__init'))
		{
			$this->__init();
		}
	}


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


	final function __isset($key)
	{
		return array_key_exists($key, $this->_matches);
	}


	/**
	* Get parameter of current route.
	* @param  int|string $param
	* @return string
	*/
	final function getParam($param)
	{
		if (strpos($param, '/'))
		{
			$param = explode('/', $param);
			foreach ($param as $key => $val) $param[$key] = self::getParam($key+1);
			return join('/', $param);
		}
		elseif (is_numeric($param))
		{
			return $this->_params[$param-1];
		}
		else
		{
			return $this->{$param};
		}
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
	 * Get link handler for current route.
	 * @return Route\Link
	 */
	final function getSelf()
	{
		return new Route\Link($this, $this->_route);
	}


	/**
	 * Returns defined routes.
	 * @return array
	 */
	final function getDefinedRoutes()
	{
		return $this->_definedRoutes;
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
	 * @param  string  $pattern    case insensitive regex pattern
	 * @param  array   $conditions array of case insensitive regex pattern bits
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
		
		if (preg_match("/{$pattern}/i", $this->_route, $matches))
		{
			$this->_matches = $matches;
			if ($matches['controller']) Framework::setController($matches['controller']);
			if ($matches['action']) Framework::setAction($matches['action']);
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
	 * Get return link.
	 * @param  string $link
	 * @param  mixed  $params
	 * @return mixed
	 */
	final function returnTo($link, $params=null)
	{
		if ($redir=$_GET['redir'])
		{
			return $this->to($redir);
		}
		else
		{
			return $this->to($link, $params);
		}
	}


	/**
	 * Get an absolute route valid for current application.
	 * @param  string     $link
	 * @param  mixed      $params
	 * @return Route\Link
	 */
	final function to($link, $params=null)
	{
		return new Route\Link($this, $link, $params);
	}
}
