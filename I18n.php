<?php
/**
 * Handles internationalization.
 *
 * @copyright  Copyright (c) 2007-2012 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_I18N
{
	static private $_base = '';
	
	static private $_locale;
	static private $_localeSections = array();
	static private $_strings = array();
	
	static $_nRule;
	
	static private $_nuberDecimalPoint = '.';
	static private $_nuberThousandsSeparator = ',';
	static private $_currencyLocal = array('$#', 2, '.', ',');
	static private $_currencyInternational = array('# USD', 2, '.', ',');
	static private $_days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	static private $_daysShort = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
	static private $_moths = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	static private $_mothsShort = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec');


	public static function setBase($base)
	{
		return self::$_base=rtrim( ($base{0}=='/') ? $base : ROOT_DIR . '/' . $base , '/' );
	}


	public static function getBase()
	{
		return ($base=self::$_base)
		     ? $base
		     : self::setBase('i18n');
	}


	static private function _setSpecialCases()
	{
		if (self::$_strings['_nRule']) self::$_nRule = create_function('$n', self::$_strings['_nRule']);
		if (self::$_strings['_nuberDecimalPoint']) self::$_nuberDecimalPoint = self::$_strings['_nuberDecimalPoint'];
		if (self::$_strings['_nuberThousandsSeparator']) self::$_nuberThousandsSeparator = self::$_strings['_nuberThousandsSeparator'];
		if (self::$_strings['_currencyLocal']) self::$_currencyLocal = self::$_strings['_currencyLocal'];
		if (self::$_strings['_currencyInternational']) self::$_currencyInternational = self::$_strings['_currencyInternational'];
		if (self::$_strings['_days']) self::$_days = self::$_strings['_days'];
		if (self::$_strings['_daysShort']) self::$_daysShort = self::$_strings['_daysShort'];
		if (self::$_strings['_moths']) self::$_moths = self::$_strings['_moths'];
		if (self::$_strings['_mothsShort']) self::$_mothsShort = self::$_strings['_mothsShort'];
	}


	static public function setLocale($locale)
	{
		self::$_locale = $locale;
		$locale_file = self::getBase() . '/' . $locale . '.php';
		if (file_exists($locale_file))
		{
			self::$_strings = include $locale_file;
			self::_setSpecialCases();
		}
	}


	static public function getLocale()
	{
		return self::$_locale;
	}


	static public function loadSection($section)
	{
		$section_file = self::getBase() . '/' . self::$_locale . '_' . $section . '.php';
		if (file_exists($section_file))
		{
			$section_strings = include $section_file;
			self::$_strings = array_merge(self::$_strings, $section_strings);
			self::$_localeSections[$section] = $section;
			return true;
		}
		else
		{
			self::$_localeSections[$section] = null;
			return false;
		}
	}


	static public function loadSectionRaw($section)
	{
		$section_file = self::getBase() . '/' . $section . '.php';
		if (file_exists($section_file))
		{
			$section_strings = include $section_file;
			self::$_strings = array_merge(self::$_strings, $section_strings);
			self::_setSpecialCases();
			return true;
		}
	}
	
	static public function t($str, $vars=array())
	{
		$str = (string)$str;
		$args = func_get_args();
		if (is_array($args[1])): $args = array_values($args[1]); else: array_shift($args); endif;
		if (is_array(self::$_strings))
		{
			if (array_key_exists($str, self::$_strings))
			{
				$str = self::$_strings[$str];
			}
			else
			{
				Lemmon_Logger::i18n($str);
			}
		}
		if ($args) $str = vsprintf($str, $args);
		$str = preg_replace('/^\w+__\s?/', '', $str);
		return $str;
	}
	
	static public function nRule($n)
	{
		if (self::$_nRule)
		{
			$nRule = self::$_nRule;
			return $nRule($n);
		}
		else
		{
			return (!$n or $n>1) ? 1 : 0;
		}
	}
	
	static public function tn($str_sg, $str_pl, $n)
	{
		if (self::$_strings[':pl ' . $str_sg])
			$strings = self::$_strings[':pl ' . $str_sg];
		elseif (is_array($str_pl))
			$strings = array_merge(array($str_sg), $str_pl);
		else
			$strings = array($str_sg, $str_pl);
		$str = sprintf($strings[ self::nRule($n) ], $n);
		$str = preg_replace('/^\w+__\s?/', '', $str);
		return $str;
	}
	
	static public function date($date, $format='d M Y')
	{
		if ($date)
		{
			$time = is_numeric($date) ? $date : strtotime($date);
			$date = date($format, $time);
		}
		return $date;
	}
	
	static public function time($date, $format='h:iA')
	{
		if ($date)
		{
			$time = is_numeric($date) ? $date : strtotime($date);
			$date = date($format, $time);
		}
		return $date;
	}
	
	static public function datetime($date)
	{
		if ($date)
		{
			$time = strtotime($date);
			$date = date('d M Y h:iA', $time);
		}
		return $date;
	}
	
	static public function price($price)
	{
		$price = number_format(
			$price,
			self::$_currencyLocal[1],
			self::$_currencyLocal[2],
			self::$_currencyLocal[3]);
		$price = str_replace('#', $price, self::$_currencyLocal[0]);
		return $price;
	}
	
	static public function priceInt($price)
	{
		$price = number_format(
			$price,
			self::$_currencyInternational[1],
			self::$_currencyInternational[2],
			self::$_currencyInternational[3]);
		$price = str_replace('#', $price, self::$_currencyInternational[0]);
		return $price;
	}
	
	static public function num($num, $dec=0)
	{
		$num = number_format(
			$num,
			$dec,
			self::$_nuberDecimalPoint,
			self::$_nuberThousandsSeparator);
		return $num;
	}
	
	static public function fileSize($size, $dec_force=null)
	{
		// B
		$dec = 0;
		$suffix = 'B';
		// kB
		if ($size>=1024)
		{
			$size = $size / 1024;
			$dec = 1;
			$suffix = 'kB';
		}
		// MB
		if ($size>=1024)
		{
			$size = $size / 1024;
			$dec = 2;
			$suffix = 'MB';
		}
		// GB
		if ($size>=1024)
		{
			$size = $size / 1024;
			$dec = 2;
			$suffix = 'GB';
		}
		return self::num($size, ($dec_force!==null) ? $dec_force : $dec) . ' ' . $suffix;
	}
	
	static public function flushIdleStrings()
	{
		
	}
}