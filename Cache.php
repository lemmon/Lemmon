<?php
/**
 * Handles caching.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_Cache
{
	static private $_base = 'cache/';
	
	public static function getBase()
	{
		return Lemmon_Autoloader::getRootDir() . '/' . self::$_base;
	}
	
	public static function setBase($base)
	{
		$base = rtrim($base, '/') . '/';
		if (!is_dir(Lemmon_Autoloader::getRootDir() . '/' . $base))
		{
			if (!mkdir(Lemmon_Autoloader::getRootDir() . '/' . $base, 0777, true)) throw new Lemmon_Exception();
		}
		self::$_base = $base;
		return self::getBase();
	}

	public static function getDir($dir=null, $create=true)
	{
		$dir = $dir ? (rtrim($dir, '/') . '/') : (Lemmon_Route::getInstance()->getController() . '/' . base_convert(date('Ymd'), 10, 36) . '/');
		if ($create and !is_dir(self::getBase() . $dir)) mkdir(self::getBase() . $dir, 0777, true);
		return $dir;
	}
	
	public static function getTmpFileName($file=null)
	{
		$ext = substr($file['name'], strrpos($file['name'], '.'));
		$t = microtime();
		$t = explode(' ', $t);
		$t = base_convert($t[1], 10, 36) . base_convert($t[0], 10, 36);
		return $t . $ext;
	}
	
	public static function put($file, $contents, $dir=null)
	{
		$dir = self::getDir($dir);
		$fp = fopen(self::getBase() . $dir . $file, 'w');
		fwrite($fp, $contents);
		fclose($fp);
		return $dir;
	}
	
	public static function get($file)
	{
		if (file_exists(self::getBase() . $file))
		{
			/*
			$fp = fopen(self::getBase() . $file, 'r');
			$contents = fread($fp, filesize(self::getBase() . $file));
			fclose($fp);
			return $contents;
			*/
			return file_get_contents(self::getBase() . $file);
		}
		else
		{
			return false;
		}
	}

	public static function headers($file, $timestamp)
	{
		$gmt_mtime = gmdate('r', $timestamp);
		header('ETag: "' . md5($timestamp . $file) . '"');
		header('Last-Modified: ' . $gmt_mtime);
		header('Cache-Control: public');
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($timestamp.$file))
			{
				header('HTTP/1.1 304 Not Modified');
				exit();
			}
		}
	}
}
