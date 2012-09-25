<?php
/**
* 
*/
class Lemmon_Array
{


	public static function first($array)
	{
		return is_array($array) ? reset($array) : null;
	}
	
	public static function last($array)
	{
		return is_array($array) ? end($array) : null;
	}
	
	public static function assoc($array, $assoc)
	{
		$res = array();
		if (is_array($array))
		{
			foreach ($array as $i => $item)
			{
				if (is_array($item))
					$res[ $item[$assoc] ] = $item;
				else
					$res[ $item ] = $item;
			}
		}
		return $res;
	}
	
	public static function hasKey($array, $key)
	{
		return array_key_exists($key, $array);
	}
	
	public static function isIn($key, $array)
	{
		return is_array($array) && in_array($key, $array);
	}
	
	public static function joinByKey($array, $key, $sep=', ')
	{
		if (is_array($array)) foreach ($array as $field)
		{
			$field = (array)$field;
			$res[] = $field[$key];
		}
		return is_array($res) ? join($sep, $res) : null;
	}

	public static function mergeSum($a1, $a2)
	{
		foreach ($a1 as $key => $val)
		{
			if ($a2[$key])
			{
				$res[$key] = $a2[$key] + $val;
			}
		}
		return $res;
	}
}
