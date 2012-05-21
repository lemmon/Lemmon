<?php
/**
* 
*/
class Lemmon_Environment
{
	static private $_development = false;
	
	static function getDevelopment()
	{
		return self::$_development;
	}
	
	static function setDevelopment($is=true)
	{
		self::$_development = (bool)$is;
	}
}
