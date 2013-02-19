<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon;

/**
 * Lemmon PHP Framework core library.
 */
class Framework
{
	private static $_instance;
	
	private static $_controller = 'index';
	private static $_action = 'index';

	protected $env;
	protected $db;
	protected $route;
	protected $template;
	protected $log;

	protected $flash;
	
	protected $data = [];


	/**
	 * Runs application.
	 * @param  Autoloader $loader
	 * @return Autoloader
	 */
	static function autoloader($loader)
	{
		// register controllers
		$loader->addMask('*_Controller', function($class){
			return '$root/app/controllers/' . strtolower(str_replace('__', DIRECTORY_SEPARATOR, preg_replace('/(.)([A-Z])/u', '$1_$2', substr($class, 0, -11)))) . '_controller.php';
		});
		// register mailers
		$loader->addMask('*Mailer', '$root/app/mailers/$file.php');
		// register application class
		$loader->addMask('Application', '$root/app/controllers/application.php');
		// register models
		$loader->add('$root/app/models/$file.php');
		//
		return $loader;
	}


	/**
	 * Sets application controller.
	 * @param string $controller
	 */
	static function setController($controller)
	{
		self::$_controller = $controller;
	}


	/**
	 * Returns application controller.
	 * @return string
	 */
	static function getController()
	{
		return self::$_controller;
	}


	/**
	 * Sets application action.
	 * @param string $action
	 */
	static function setAction($action)
	{
		self::$_action = $action;
	}


	/**
	 * Returns application action.
	 * @return string
	 */
	static function getAction()
	{
		return self::$_action;
	}


	/**
	 * Run the application.
	 * @param array $params
	 */
	static function run(array $params = [])
	{
		// controller
		$controller_name = self::$_controller;
		$action_name = self::$_action;
		
		$controller_class_name = str_replace(['. ', ' '], ['_', ''], ucwords(str_replace(['/', '_'], ['. ', ' '], $controller_name))) . '_Controller';
		$action_method_name = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $action_name))));
		
		// create controller
		$controller = new $controller_class_name($params);
		
		// common template data
		$controller->data = [
			'f'     => $_POST,
			'flash' => $controller->getFlash(),
			'link'  => $controller->getRoute(),
		];
		
		// template (legacy)
		Template::appendFilesystem('app/views/' . $controller_name);

		// init controller
		if (($res = $controller->__initApplication()) === null and ($res = $controller->__init()) === null)
		{
			// find action
			if (method_exists($controller, $action_method_name))
			{
				// execute action
				$res = $controller->{$action_method_name}();
			}
			else
			{
				// error on missing action
				throw new \Lemmon_Exception(sprintf('Unknown method `%s()` on `%s`', $action_method_name, get_class($controller)));
			}
		}
		
		// process the result
		if (($res === null and $controller->template instanceof Template\Template) or ($res instanceof Template\Template))
		{
			$template = ($res) ?: $controller->template;
			$html = $template->render($controller->data);
			echo $html;
		}
		elseif ($res === null)
		{
			$html = Template::display($action_name, $controller->getData(true));
			echo $html;
		}
		elseif ($res instanceof Route\Link)
		{
			$controller->request->redir((string)$res)->exec();
		}
		elseif ($res instanceof Request\Redir)
		{
			// redirect
			$res->exec();
			exit;
		}
		elseif (is_string($res) or is_int($res))
		{
			// display plain text result
			#header('Content-Type: text/plain');
			echo $res;
		}
		elseif ($res)
		{
			dump($res);
		}
	}


	protected function __initApplication(){}
	protected function __init(){}


	/**
	 * Runs application.
	 * @return string
	 */
	final function getData($include_data = false)
	{
		$data = $this->data;
		// include framework's data
		if ($include_data)
		{
			$data['link']  = $this->route;
			$data['flash'] = $_SESSION['__FLASH__'];
			#$data['f']     = array_merge_recursive($_POST, (array)$data['f']);
		}
		//
		return $data;
	}


	final function setData(array $data)
	{
		$this->data = array_merge($this->data, $data);
	}


	/**
	 * Runs application.
	 * @return string
	 */
	final function __construct(array $params = [])
	{
		// assign necessary classes
		foreach ($params as $key => $param)
		{
			$this->{$key} = $param;
		}
		
		// create rest of the classes
		$this->flash   = new Flash($this->route);
		$this->request = new Request($this);

		// instance
		self::$_instance = $this;
	}


	/**
	 * Returns current application instance.
	 * @return Framework
	 */
	static function getInstance()
	{
		return self::$_instance;
	}


	/**
	 * @return Route
	 */
	function getRoute()
	{
		return $this->route;
	}


	function getRequest()
	{
		return $this->request;
	}


	/**
	 * @return Flash
	 */
	function getFlash()
	{
		return $this->flash;
	}
}
