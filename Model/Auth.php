<?php
/**
 * Handles authentication.
 *
 * @copyright  Copyright (c) 2007-2011 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_Model_Auth extends Lemmon_Model
{
	private static $_this;
	
	protected function onAuth()
	{
		return $this;
	}
	
	final public static function doAuth($f)
	{
		if ($user=parent::make()->onAuth($f)->first())
		{
			return self::$_this = $_SESSION['__LEMMON_AUTH__'] = $user;
		}
		return false;
	}
	
	final public static function getCurrent()
	{
		if ($user=self::$_this)
		{
			return $user;
		}
		elseif ($user=$_SESSION['__LEMMON_AUTH__'] and get_called_class()==get_class($user))
		{
			return self::$_this = $user;
		}
		else
		{
			return false;
		}
	}
	
	final public static function setCurrent($id)
	{
		return self::$_this = $_SESSION['__LEMMON_AUTH__'] = self::make($id)->first();
	}
	
	final public static function clearCurrent()
	{
		self::$_this = $_SESSION['__LEMMON_AUTH__'] = null;
		unset($_SESSION['__LEMMON_AUTH__']);
	}
}
