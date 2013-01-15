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
	protected $_join = [];
	protected $_where = [];
	protected $_group;
	protected $_order;
	protected $_limit;
	protected $_offset;


	function __construct($query, $table = null)
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
			$this->_join   = $query->_join;
			$this->_where  = $query->_where;
			$this->_group  = $query->_group;
			$this->_order  = $query->_order;
			$this->_limit  = $query->_limit;
			$this->_offset = $query->_offset;
		}
		else
		{
			throw new \Exception('Unknown query type.');
		}
		
		// table
		if ($table) $this->setTable($table);
	}


	function __toString()
	{
		return $this->getQueryString();
	}


	function toString()
	{
		return $this->getQueryString();
	}


	function setTable($table, $alias = null)
	{
		if (is_array($table))
		{
			if (is_int(key($table)))
			{
				$this->_table = new Table(current($table));
			}
			else
			{
				$this->_table = new Table(current($table), key($table));
			}
		}
		else
		{
			$this->_table = new Table($table, $alias);
		}
	}


	function getTable()
	{
		return $this->_table;
	}


	function join($table, $arg, $value = null)
	{
		if (is_array($arg))
		{
			$this->_join[] = new Join($this->getTable(), $table, $arg);
		}
		else
		{
			$this->_join[] = new Join($this->getTable(), $table, [$arg => $value]);
		}
		$this->getTable()->forceName(true);
	}


	/* DEV helper function */
	function _J()
	{
		return $this->_join;
	}
	/* /DEV */


	function where($expr, $value = false)
	{
		if ($expr)
		{
			if (is_array($expr))
			{
				foreach ($expr as $_expr => $_value)
				{
					if (is_numeric($_expr))
					{
						if (!is_array($_value))
						{
							$this->_where[] = new Where($this->getTable(), $_value);
						}
						else
						{
							throw new \Exception('This kind of array is not supported.');
						}
					}
					else
					{
						$this->_where[] = new Where($this->getTable(), $_expr, $_value);
					}
				}
			}
			elseif (func_num_args() > 2)
			{
				$_where = new \ReflectionClass(__NAMESPACE__ . '\Where');
				$this->_where[] = $_where->newInstanceArgs(array_merge([$this->getTable()], func_get_args()));
			}
			else
			{
				$this->_where[] = new Where($this->getTable(), $expr, $value);
			}
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


	function group($group)
	{
		$this->_group = new Expression($group);
		return $this;
	}


	function order($order)
	{
		$this->_order = new Expression($order);
		/*
		$order = join(', ', is_array($order) ? $order : func_get_args());
		preg_match_all('/([\w\.]+)\s*(asc|desc)?/i', $order, $m);
		foreach ($m[0] as $i => $_order)
		{
			$m[0][$i] = str_replace($m[1][$i], Quote::field($m[1][$i]), $m[0][$i]);
		}
		$this->_order = join(', ', $m[0]);
		*/
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


	function exec()
	{
		return $this->_query->exec($this->getQueryString());
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
