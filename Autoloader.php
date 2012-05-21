<?php
/**
* 
*/
class Lemmon_Autoloader
{
	private static $_rootDir;
	private static $_libDir;

	private $_masks = array();
	private $_dirs = array();
	
	function __construct($prepend=false)
	{
		// controllers
		$this->register('*_Controller', function($class){
			return '$root/app/controllers/' . Lemmon_Autoloader::classToFileName(substr($class, 0, -11)) . '_controller.php';
		});
		// mailers
		$this->register('*Mailer', '$root/app/mailers/$file.php');
		// application
		$this->register('Application', '$root/app/controllers/application.php');
		// models
		$this->registerDir('$root/app/models/$file.php');
		// general
		$this->registerDir('$lib/$class.php');
		//
		// register
		spl_autoload_register(array($this, 'loadClass'), true, $prepend);
	}
	
	static function setRootDir($dir)
	{
		self::$_rootDir = rtrim($dir, DIRECTORY_SEPARATOR);
	}
	
	static function getRootDir()
	{
		return self::$_rootDir;
	}
	
	static function setLibDir($dir)
	{
		self::$_libDir = rtrim($dir, DIRECTORY_SEPARATOR);
	}
	
	static function getLibDir()
	{
		return self::$_libDir;
	}
	
	static function classToFileName($str)
	{
		$str = strtolower(str_replace('__', DIRECTORY_SEPARATOR, preg_replace('/(.)([A-Z])/u', '$1_$2', $str)));
		return $str;
	}
	
	function register($mask, $file)
	{
		$this->_masks['/^' . str_replace('*', '.+', $mask) . '$/'] = $file;
	}
	
	function registerDir($file)
	{
		$this->_dirs[] = $file;
	}
	
	function loadClass($class)
	{
		if ($file=$this->findFile($class)) require $file;
	}
	
	private function _parse($file, $class)
	{
		return str_replace(array('$root', '$lib', '$class', '$file'), array(self::$_rootDir, self::$_libDir, str_replace('_', DIRECTORY_SEPARATOR, $class), self::classToFileName($class)), $file);
	}
	
	function findFile($class, &$dirs=array())
	{
		// masks
		foreach ($this->_masks as $mask => $file) if (preg_match($mask, $class))
		{
			if (is_callable($file)) $file = $file($class);
			$file = $this->_parse($file, $class);
			if (is_file($file)) return $file;
		}
		// dirs
		foreach ($this->_dirs as $file)
		{
			$file = $this->_parse($file, $class);
			if (is_file($file)) return $file;
		}
	}
}
