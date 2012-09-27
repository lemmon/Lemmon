<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Sql;

/**
 * SQL Select.
 */
abstract class QueryStatement
{


	function __call($name, $args)
	{
		throw new \Exception(sprintf('[todo] Missing method %s()', $name));
		/*
		if (method_exists($this->_query, $name))
		{
			return call_user_func_array([$this->_query, $name], $args);
		}
		else
		{
			throw new \Exception(sprintf('Unknown method %s() on %s class.', $name, get_class($this)));
		}
		*/
	}


	function expr($expression)
	{
		return new Expression($this, func_get_args());
	}


	function quoteField($field)
	{
		$field = explode('.', $field);
		foreach ($field as $i => $_field)
		{
			if ($_field!='*')
			{
				$field[$i] = '`' . $_field . '`';
			}
		}
		return join('.', $field);
	}


	function quote($value)
	{
		if (is_null($value))
		{
			return 'NULL';
		}
		elseif (is_array($value))
		{
			return join(', ', $this->quoteArray($value));
		}
		elseif (is_int($value) or is_double($value))
		{
			return $value;
		}
		else
		{
			return sprintf('\'%s\'', addslashes($value));
		}
	}


	function quoteArray(array $array)
	{
		foreach ($array as $key => $val)
		{
			$array[$key] = $this->quote($val);
		}
		return $array;
	}
}
