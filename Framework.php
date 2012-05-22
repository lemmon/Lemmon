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
 * Lemmon PHP Framework core library.
 */
class Framework
{
	private static $_instance;

	protected $log;
	protected $env;
	protected $db;
	protected $route;
	protected $tpl;

	protected $flash;
	
	protected $data = array();


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
	 * Run the application.
	 * @param array $params
	 */
	static function run(array $params=null)
	{
		try
		{
			// start sessions
			session_start();
	
			// controller
			$controller_name = $params['route']->getController();
			$action_name     = $params['route']->getAction();
			
			$controller_class_name = str_replace(array('. ', ' '), array('_', ''), ucwords(str_replace(array('/', '_'), array('. ', ' '), $controller_name))) . '_Controller';

			// create controller
			$controller = new $controller_class_name($params);

			// init controller
			if (method_exists($controller, '__init'))
			{
				$controller->__init();
			}
			
			// execute action
			if (method_exists($controller, $action_name))
			{
				$res = $controller->{$action_name}();
			}
			else
			{
				throw new \Lemmon_Exception(sprintf('Unknown method `%s` on `%s`', $action, get_class($controller)));
			}
		}
		catch (\Exception $exception)
		{
			// handle the exception
			$trace = $exception->getTrace();
			if ($trace[0]['file'])
			{
				$trace[0]['block'] = array_slice(file($trace[0]['file']), $trace[0]['line']-8, 15, true);
			}
			Template::display(LIBS_DIR . '/Lemmon/Template/exception.html', array(
				'exception' => $exception,
				'exception_block' => array_slice(file($exception->getFile()), $exception->getLine()-8, 15, true),
				'trace' => $trace,
			));
			exit;
		}
		
		// process result
		if ($res === null)
		{
			// load template
			$data = $controller->data;
			$data['link']  = $controller->route;
			$data['flash'] = $_SESSION['_flash']['message'];
			$data['f']     = array_merge_recursive($_POST, (array)$data['f']);
			Template::appendFilesystem('app/views/' . $controller_name);
			Template::display($action_name, $data);
		}
		elseif ($res instanceof Request\Redir)
		{
			// redirect
			$res->exec();
			exit;
		}
		elseif ($res instanceof Lemmon_Mailer)
		{
			Template::display(LIBS_DIR . '/Lemmon/Template/exception.html', array(
				'message' => $res,
				'link'    => $controller->route,
			));
		}
		else
		{
			// display plain text result
			echo $res;
		}
	}


	/**
	 * Runs application.
	 * @return string
	 */
	final function __construct(array $params=null)
	{
		// assign necessary classes
		$this->log   = $params['log'];
		$this->db    = $params['db'];
		$this->env   = $params['env'];
		$this->route = $params['route'];
		
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


	/**
	 * @return Flash
	 */
	function getFlash()
	{
		return $this->flash;
	}
}
