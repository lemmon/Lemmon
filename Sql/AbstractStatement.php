<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Sql;

/**
 * SQL Select.
 */
abstract class AbstractStatement implements StatementInterface
{
	protected $_query;
	protected $_table;
	protected $_where = [];
	protected $_order;
	protected $_limit;
	protected $_offset;


	function __construct($query, $table=null)
	{
		// query
		if ($query instanceof Query)
		{
			$this->_query = $query;
		}
		elseif ($query instanceof Statement)
		{
			$this->_query  = $query->_query;
			$this->_table  = $query->_table;
			$this->_where  = $query->_where;
			$this->_order  = $query->_order;
			$this->_limit  = $query->_limit;
			$this->_offset = $query->_offset;
		}
		else
		{
			throw new \Exception('Unknown query type.');
		}
		
		// table
		if ($table) $this->from($table);
	}


	function __toString()
	{
		return $this->getQueryString();
	}


	function from($table)
	{
		$this->_table = new Table(is_array($table) ? $table : func_get_args());
		return $this;
	}


	function where($expr, $value=false)
	{
		if (is_array($expr))
		{
			foreach ($expr as $_expr => $_value)
			{
				if (is_numeric($_expr))
				{
					if (!is_array($_value))
					{
						$this->_where[] = new Where($_value);
					}
					else
					{
						throw new \Exception('This kind of array is not supported.');
					}
				}
				else
				{
					$this->_where[] = new Where($_expr, $_value);
				}
			}
		}
		elseif (func_num_args()>2)
		{
			$_where = new \ReflectionClass(__NAMESPACE__ . '\Where');
			$this->_where[] = $_where->newInstanceArgs(func_get_args());
		}
		else
		{
			$this->_where[] = new Where($expr, $value);
		}
		return $this;
	}


	/* DEV helper function */
	function W()
	{
		$w = [];
		foreach ($this->_where as $where) $w[] = $where->getExpression();
		return $w;
	}
	/* /DEV */


	function order($order)
	{
		$order = join(', ', is_array($order) ? $order : func_get_args());
		preg_match_all('/([\w\.]+)\s*(asc|desc)?/i', $order, $m);
		foreach ($m[0] as $i => $_order)
		{
			$m[0][$i] = str_replace($m[1][$i], Quote::field($m[1][$i]), $m[0][$i]);
		}
		$this->_order = join(', ', $m[0]);
		return $this;
	}


	function limit($limit)
	{
		if (!is_numeric($limit))
		{
			throw new \Exception('Limit can only be an integer value.');
		}
		$this->_limit = (int)$limit;
		return $this;
	}


	function offset($offset)
	{
		if (!is_numeric($offset))
		{
			throw new \Exception('Offset can only be an integer value.');
		}
		$this->_offset = (int)$offset;
		return $this;
	}


	function __call($name, $args)
	{
		throw new \Exception(sprintf('[todo] Missing method %s().', $name));
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
}
