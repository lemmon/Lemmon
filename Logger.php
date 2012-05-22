<?php
/**
*
*/
class Lemmon_Logger
{
	private static $_instance;
	
	private static $_base;

	protected $_path = 'log';
	protected $_file = '%Y-%m-%d-%Hh.txt';
	protected $_time = 'Y-m-d H:i:s';
	
	private $_cache;
	
	public function __construct()
	{
		self::$_instance = $this;
		
		$this->define();
	}
	
	public static function getInstance()
	{
		return self::$_instance;
	}
	
	public static function __callStatic($name, $arguments)
	{
		if ($instance=self::$_instance)
		{
			if (method_exists($instance, '_log' . $name))
			{
				call_user_func_array(array($instance, '_log'.$name), $arguments);
			}
		}
	}
	
	protected function define() {}
	protected function _logError() {}
	protected function _logNotice() {}
	protected function _logWarning() {}
		
	public function setBase($base)
	{
		return self::$_base=rtrim( ($base{0}=='/') ? $base : ROOT_DIR . '/' . $base , '/' );
	}
	
	public function getBase()
	{
		return ($base=self::$_base)
		     ? $base
		     : $this->setBase($this->_path);
	}
	
	protected function _log($type, $message, $params=array())
	{
		$file = array_key_exists('file', $params) ? $params['file'] : $this->_file;
		$time = array_key_exists('time', $params) ? $params['time'] : $this->_time;
		$fp = fopen(self::getBase() . '/' . strftime($file), 'a');
		fwrite($fp, $type . ' -- ' . date($time) . ' -- ' . $message . "\n");
		fclose($fp);
	}
	
	protected function _logRaw($message, $params=array())
	{
		$file = array_key_exists('file', $params) ? $params['file'] : $this->_file;
		$fp = fopen(self::getBase() . '/' . strftime($file), 'a');
		fwrite($fp, $message . "\n");
		fclose($fp);
	}
}
