<?php
/**
 * General strings helpers.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_String
{
	

	static public function human($str)
	{
		$str = str_replace('_id', '', $str);
		$str = str_replace(array('-', '_', '.'), ' ', $str);
		$str = ucwords($str);
		return $str;
	}

	
	static public function parseCode($code)
	{
		return '[foo]';
	}

	
	static public function urlLink($url)
	{
		if (substr($url, 0, 7)!='http://' and substr($url, 0, 8)!='https://') $url = 'http://' . $url;
		return $url;
	}
	

	static public function urlCaption($url)
	{
		if (substr($url, 0, 7)=='http://') $url = substr($url, 7);
		return $url;
	}
	
	
	public static function html($html)
	{
		return $html;
	}
	

	public static function text($text)
	{
		if ($text)
		{
			$text = trim($text);
			$text = preg_replace('/(\r?\n){2,}/', '</p><p>', $text);
			$text = preg_replace('/(\r?\n)/', '<br>', $text);
			$text = $text ? ('<p>' . $text . '</p>') : '';
			// match E-mails
			$text = preg_replace('/([\w]+)@([\w]{2,})\.([\w\.]{2,})/i', "$1[at]$2[dot]$3", $text);
			// match URLs
			$text = preg_replace('/(https?:\/\/)?(([\w\-]+\.)*([a-z]{2,}\.)([a-z]{2,4})(\/[\w\.\-\?=\/&;,#!:]*)?)(\W)/i', "<a href=\"http://$2\" target=\"_blank\" rel=\"external nofollow\">$2</a>$7", $text);
		}
		return $text;
	}
	
	
	static public function nl2br($str)
	{
		$str = trim($str);
		$str = nl2br($str);
		return $str;
	}


	public static function indent($str)
	{
		$res = preg_replace('/([\.]+\s+)/', '', trim($str), -1, $n);
		return $n;
	}
	
	
	public static function removeIndent($str)
	{
		$res = preg_replace('/([\.]+\s+)/', '', trim($str), -1, $n);
		return $res;
	}
	
	
	static public function line($str, $len=150)
	{
		$str = strip_tags($str);
		$str = trim(preg_replace('/\s+/', ' ', $str));
		if ($len>0 and strlen($str)>$len) $str = preg_replace('/\W+$/', '', substr($str, 0, $len), 1) . '...';
		return $str;
	}


	static public function sg($pl)
	{
		if (substr($pl, -4)=='vies')
		{
			$sg = substr($pl, 0, -1);
		}
		elseif (substr($pl, -3)=='ies')
		{
			$sg = substr($pl, 0, -3) . 'y';
		}
		elseif (substr($pl, -2)=='xes')
		{
			$sg = substr($pl, 0, -2);
		}
		else
		{
			$sg = substr($pl, 0, -1);
		}
		return $sg;
	}

	
	static public function pl($sg)
	{
		if (substr($sg, -1)=='y')
		{
			$pl = substr($sg, 0, -1) . 'ies';
		}
		elseif (substr($sg, -1)=='x')
		{
			$pl = $sg . 'es';
		}
		elseif (substr($sg, -2)=='en')
		{
			$pl = substr($sg, 0, -3);
		}
		else
		{
			$pl = $sg . 's';
		}
		return $pl;
	}

	
	static public function classToFileName($str)
	{
		$str = strtolower(preg_replace('/(.)([A-Z])/u', '$1_$2', $str));
		return $str;
	}


	static public function classToTableName($str)
	{
		return self::classToFileName($str);
	}

	
	static public function tableToClassName($str, $table=null)
	{
		$str = str_replace('@', $table . ' ', $str);
		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
		return $str;
	}


	public static function entities($str)
	{
		$str = str_replace('ľ', '&#x013e;', $str);
		return $str;
	}
	
	static public function asciize($str, $sep='-', $lower=true)
	{
		$str = preg_replace('/[àáâäæãåā]/u', 'a', $str);
		$str = preg_replace('/[ÀÁÂÄÆÃÅĀ]/u', 'A', $str);
		$str = preg_replace('/[çćč]/u',      'c', $str);
		$str = preg_replace('/[ÇĆČ]/u',      'C', $str);
		$str = preg_replace('/[ď]/u',        'd', $str);
		$str = preg_replace('/[Ď]/u',        'D', $str);
		$str = preg_replace('/[èéêëēėę]/u',  'e', $str);
		$str = preg_replace('/[ÈÉÊËĒĖĘ]/u',  'E', $str);
		$str = preg_replace('/[îïíīįì]/u',   'i', $str);
		$str = preg_replace('/[ÎÏÍĪĮÌ]/u',   'I', $str);
		$str = preg_replace('/[ľĺł]/u',      'l', $str);
		$str = preg_replace('/[ĽĹŁ]/u',      'L', $str);
		$str = preg_replace('/[ňñń]/u',      'n', $str);
		$str = preg_replace('/[ŇÑŃ]/u',      'N', $str);
		$str = preg_replace('/[ôöòóœøōõ]/u', 'o', $str);
		$str = preg_replace('/[ÔÖÒÓŒØŌÕ]/u', 'O', $str);
		$str = preg_replace('/[ŕř]/u',       'r', $str);
		$str = preg_replace('/[ŔŘ]/u',       'R', $str);
		$str = preg_replace('/[śšß]/u',      's', $str);
		$str = preg_replace('/[ŚŠ]/u',       'S', $str);
		$str = preg_replace('/[ť]/u',        't', $str);
		$str = preg_replace('/[Ť]/u',        'T', $str);
		$str = preg_replace('/[ůûüùúū]/u',   'u', $str);
		$str = preg_replace('/[ŮÛÜÙÚŪ]/u',   'U', $str);
		$str = preg_replace('/[ýÿ]/u',       'y', $str);
		$str = preg_replace('/[ÝŸ]/u',       'Y', $str);
		$str = preg_replace('/[žźż]/u',      'z', $str);
		$str = preg_replace('/[ŽŹŻ]/u',      'Z', $str);
		$str = preg_replace('/[\W_]+/', $sep, $str);
		if ($lower)
		{
			$str = strtolower($str);
		}
		$str = trim($str, $sep);
		return $str;
	}	
}
