<?php

namespace Lemmon;

/**
 * General strings helper functions.
 *
 * @copyright  Copyright (c) 2007-2012 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class String
{


	/**
	 * Converts variable name to human representation.
	 */
	static public function human($str)
	{
		$str = str_replace('_id', '', $str);
		$str = str_replace(array('-', '_', '.'), ' ', $str);
		$str = ucwords($str);
		return $str;
	}


	/**
	 * Returns html.
	 * @param  string  $html
	 * @return string
	 * @todo   it just returns $html variable
	 */
	public static function html($html)
	{
		return $html;
	}


	/**
	 * Converts plain text to html.
	 * @param  string  $text
	 * @return string
	 * @todo   probably merge text() & html() to textToHtml()
	 */
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


	static function html2text($html)
	{
		$html = preg_replace('/[\s]+/', ' ', $html);
		$html = preg_replace('/\s+(<(p|h1|h2|h3|h4|h5|h6|table))/', "\n\n$1", $html);
		$html = preg_replace('/\s+(<(tr))/', "\n$1", $html);
		$html = preg_replace('/(<br>)/', "$1\n", $html);
		$text = strip_tags($html);
		$text = trim($text);
		$text = preg_replace('/[ ]*\n[ ]*/', "\n", $text);
		$text = preg_replace('/[\n]{3,}/', "\n\n", $text);
		return $text;
	}


	/**
	 * Converts plain text or html to single line string.
	 * @param  string  $text
	 * @param  int     $len
	 * @return string
	 * @todo   perhaps better is to convert "..." to &hellip; or test it with Twig what's best
	 */
	static public function line($str, $len=150)
	{
		$str = strip_tags($str);
		$str = trim(preg_replace('/\s+/', ' ', $str));
		if ($len>0 and strlen($str)>$len) $str = preg_replace('/\W+$/', '', substr($str, 0, $len), 1) . '...';
		return $str;
	}


	static function paragraph($text)
	{
		$text = trim($text);
		$text = str_replace("\r\n", "\n", $text);
		$text = preg_replace('/[ ]*\n[ ]*/', "\n", $text);
		$text = explode("\n\n", $text);
		return $text[0];
	}


	/**
	 * Converts plural string to singular.
	 * @param  string  $pl
	 * @return string
	 */
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


	/**
	 * Converts singular string to plural.
	 * @param  string  $sg
	 * @return string
	 */
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


	/**
	 * Converts class name to filename.
	 * @param  string  $str
	 * @return string
	 */
	static public function classToFileName($str)
	{
		return (str_replace('__', DIRECTORY_SEPARATOR, preg_replace('/(.)([A-Z])/u', '$1_$2', $str)));
	}


	/**
	 * Converts class name to table name.
	 * @param  string  $str
	 * @return string
	 */
	static public function classToTableName($str)
	{
		return strtolower(self::classToFileName($str));
	}


	/**
	 * Converts table name to class name.
	 * @param  string  $str
	 * @param  string  $table
	 * @return string
	 */
	static public function tableToClassName($str, $table='')
	{
		$str = str_replace('@', $table . ' ', $str);
		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
		return $str;
	}


	/**
	 * Asciizes string.
	 * @param  string  $str
	 * @param  string  $sep
	 * @param  string  $lower
	 * @return string
	 */
	static public function asciize($str, $sep='-', $lower=true)
	{
		// asciize
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
		// lowecase everything?
		if ($lower) $str = strtolower($str);
		// trim
		$str = trim($str, $sep);
		//
		return $str;
	}	
}
