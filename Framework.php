<?php
/**
* This file is part of the Lemmon Framework.
*
* Copyright 2007-2011, Jakub Pelák (http://www.lemmonjuice.com)
*/

/**
 * Lemmon PHP Framework core library.
 *
 * @copyright  Copyright (c) 2007-2011 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_Framework
{
	private static $_instance;
	private static $_root;

	protected $_name = 'Lemmon Framework Application';
	protected $_version = null;
	protected $_frameworkVersion = '?';

	protected $route;
	protected $viewRoot;
	
	private $_viewRootDisabled;

	protected $private = array();

	public $data = array();


	public function __construct()
	{
		// instance
		self::$_instance = $this;

		// load route
		$this->route = Lemmon_Route::getInstance();
		
		// magic quotes fix
		$_GET = $this->_checkQuotes($_GET);
		$_POST = $this->_checkQuotes($_POST);

		// start sessions
		session_start();
		// template
		$this->_view($this->route->getAction());
		// flash messages
		$currentHash = self::_getCurrentHash();
		if ($currentHash!=$_SESSION['_flash']['link']) $_SESSION['_flash'] = array( 'link' => $currentHash );
	}
	
	
	static public function getInstance()
	{
		return self::$_instance;
	}
	
	
	private function _checkQuotes($data)
	{
		if (get_magic_quotes_gpc())
		{
			if (is_array($data))
				foreach ($data as $key => $value) $data[$key] = $this->_checkQuotes($value);
			else
				$data = stripslashes(trim($data));
		}
		return $data;
	}
	
	
	public static function getName()
	{
		return self::getInstance()->_name;
	}
	
	static public function go()
	{
		$route = Lemmon_Route::getInstance();
		
		try
		{
			// load controller
			$controller_class_name = str_replace(array('. ', ' '), array('_', ''), ucwords(str_replace(array('/', '_'), array('. ', ' '), $route->getController()))) . '_Controller';
			$controller = new $controller_class_name();
			
			$action = $route->getAction();
			
			// execute action
			if (method_exists($controller, $action))
			{
				$res = $controller->{$action}();
			}
			else
			{
				throw new Lemmon_Exception(sprintf('Unknown method `%s` on `%s`', $action, get_class($controller)));
			}
		}
		catch (Exception $exception)
		{
			$trace = $exception->getTrace();
			if ($trace[0]['file'])
			{
				$trace[0]['block'] = array_slice(file($trace[0]['file']), $trace[0]['line']-8, 15, true);
			}
			Lemmon_Template::display('exception', array(
				'exception' => $exception,
				'exception_block' => array_slice(file($exception->getFile()), $exception->getLine()-8, 15, true),
				'trace' => $trace,
			));
			exit;
		}

		if ($res===null)
		{
			// load template
			Lemmon_Template::display();
		}
		elseif ($res instanceof Lemmon_Route_Redir)
		{
			// redirect
			$res->exec();
			exit;
		}
		elseif ($res instanceof Lemmon_Mailer)
		{
			Lemmon_Template::display('email', array(
				'message' => $res,
			));
		}
		else
		{
			// display plain result
			echo $res;
		}
	}
	

	static public function error($errno, $errstr, $error_file, $error_line, $context)
	{
		if (!(error_reporting() & $errno)) return;
		throw new ErrorException($errstr, 0, $errno, $error_file, $error_line);
	}


	protected function _view($view=null, $view_root=null)
	{
		$route = Lemmon_Route::getInstance();
		if ($view_root) $this->viewRoot = $view_root;
		if ($this->viewRoot===null) $this->viewRoot = $route->getController();
		if ($view) $this->view = strtr($view, '-', '_');
		return ($this->_viewRootDisabled ? '' : $this->viewRoot . '/') . $this->view . '.html';
	}
	
	
	public function setView($a, $b=null)
	{
		$this->_view($a, $b);
	}
	
	
	public function getView()
	{
		return $this->_view();
	}
		

	protected function _viewRoot($view_root=null)
	{
		if ($view_root) $this->viewRoot = $view_root;
		return $this->viewRoot;
	}
	

	public function getViewRoot($view_root=null)
	{
		return ($this->_viewRootDisabled ? '' : ($view_root ? $view_root : $this->viewRoot) . '/');
	}
	

	public function disableViewRoot()
	{
		$this->_viewRootDisabled = true;
	}
	

	public function allOk()
	{
		return 1;
	}
	

	private function _getCurrentHash()
	{
		return md5( ($_POST ? microtime(1) : '') . $this->route->getSelfWithParams() );
	}
	

	public function flash($type, $line, $key=null)
	{
		if ($line)
		{
			if ($key)
				$_SESSION['_flash']['message'][$type][$key] = $line;
			else
				$_SESSION['_flash']['message'][$type][] = $line;
		}
	}

	
	public function flashNotice()
	{
		$_SESSION['_flash']['message']['notice'][] = call_user_func_array('Lemmon_I18n::t', func_get_args());
		return $this;
	}

	
	public function flashError()
	{
		$_SESSION['_flash']['message']['error'][] = call_user_func_array('Lemmon_I18n::t', func_get_args());
		return $this;
	}

	
	public function flashErrorField()
	{
		foreach (func_get_args() as $field) $_SESSION['_flash']['error_field'][$field] = $field;
		return $this;
	}


	static public function getIp()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
}
