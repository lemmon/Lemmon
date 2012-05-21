<?php
/**
* 
*/
class Lemmon_Template
{
	private static $_filesystem;
	private static $_filesystemAppended = array();
	private static $_environment = array();
	private static $_cache = 'tpl';
	
	private static function _getFilesystem()
	{
		if (!($filesystem=self::$_filesystem))
		{
			$filesystem = array( Lemmon_Autoloader::getRootDir() . '/app/views', Lemmon_Autoloader::getLibDir() . '/Lemmon/Template' );
		}
		if ($filesystem_appended=self::$_filesystemAppended)
		{
			$filesystem = array_merge($filesystem_appended, $filesystem);
		}
		return $filesystem;
	}
	
	public static function setFilesystem($filesystem, $include_lemmon=false)
	{
		$filesystem = array(Lemmon_Autoloader::getRootDir() . '/' . $filesystem);
		if ($include_lemmon) $filesystem[] = Lemmon_Autoloader::getLibDir() . '/Lemmon/Template';
		self::$_filesystem = $filesystem;
	}
	
	public static function appendFilesystem($filesystem_to_append)
	{
		array_unshift(self::$_filesystemAppended, Lemmon_Autoloader::getRootDir() . '/' . $filesystem_to_append);
	}
	
	public static function getEnvironment()
	{
		$environment = self::$_environment;
		$environment['cache'] = Lemmon_Cache::getBase() . self::$_cache;
		return $environment;
	}
	
	public static function setEnvironment($environment)
	{
		self::$_environment = $environment;
	}

	public static function display($tpl_file=null, $data=array())
	{
		$controller = Lemmon_Framework::getInstance();
		$route = Lemmon_Route::getInstance();
		
		$template_loader = new Twig_Loader_Filesystem(self::_getFilesystem());
		$template_environment = new Twig_Environment($template_loader, self::getEnvironment());
		$template_environment->addExtension(new Lemmon_Template_Extension());
		$template = $template_environment->loadTemplate($tpl_file ? $tpl_file . '.html' : $controller->getView());
		$data = array_merge($data, (array)$controller->data);
		$data['link'] = $route;
		$data['flash'] = $_SESSION['_flash']['message'];
		$data['f'] = array_merge($_POST, (array)$controller->data['f']);

		return $template->display($data);
	}

	public static function render($tpl, $data=array())
	{
		$controller = Lemmon_Framework::getInstance();
		$route = clone Lemmon_Route::getInstance();
		
		$template_file = $controller->getViewRoot() . $tpl;
		$template_loader = new Twig_Loader_Filesystem(self::_getFilesystem());
		$template_environment = new Twig_Environment($template_loader, self::getEnvironment());
		$template_environment->addExtension(new Lemmon_Template_Extension());
		$template = $template_environment->loadTemplate($template_file);
		$data['link'] = $route;
		$data['flash'] = $_SESSION['_flash']['message'];
		$data['f'] = array_merge($_POST, (array)$controller->data['f']);

		return $template->render($data);
	}
	
	public static function debug($dump)
	{
		return '<pre class="debug">' . print_r($dump, 1) . '</pre>';
	}
	
	public static function varDump($dump)
	{
		return '<pre class="debug">' . var_export($dump, 1) . '</pre>';
	}
}