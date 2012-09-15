<?php


function dump($dump)
{
	echo '<pre>'.print_r($dump, 1).'</pre>';
}


function _t($line)
{
	return call_user_func_array('Lemmon_I18n::t', func_get_args());
}


/*
function param($object, $param)
{
	if (is_object($object)) return $object->$param;
	elseif (is_array($object)) return $object[$param];
}

function paragraph($text)
{
	$text = preg_replace('/[\r]+/', '', $text);
	$text = preg_split('/[\n]{2,}/', $text);
	foreach ($text as $i => $p)
	{
		if (substr($p, 0, 4)=='### ')
		{
			$p = '<h3>'.substr($p, 4).'</h3>';
		}
		else
		{
			$p = '<p>'.nl2br($p).'</p>';
		}
		$text[$i] = $p;
	}
	$text = join($text);
	return $text;
}

function array_merge_sum($a1, $a2)
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

function my_json($in)
{
	if (is_array($in) or is_object($in))
	{
		return json_encode($in);
	}
	elseif (is_string($in))
	{
		return json_decode($in);
	}
}

function first_in_array($array)
{
	return reset($array);
}
*/
