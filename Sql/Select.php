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
class Select extends QueryStatement
{
	private $_query;
	private $_table;
	private $_select = '*';
	private $_where = [];
	private $_order;
	private $_limit;
	private $_offset;


	function __construct($query, $table=null)
	{
		// query
		$this->_query = $query;
		
		// table
		if ($table) $this->from($table);
	}


	function __toString()
	{
		$q = [];
		// select
		$q[] = 'SELECT ' . (is_array($this->_select) ? join(', ', $this->_select) : $this->_select);
		// from
		$q[] = 'FROM ' . $this->_table;
		// where
		if ($this->_where) $q[] = 'WHERE ' . join(' AND ', $this->_where);
		// order
		if ($this->_order) $q[] = 'ORDER BY ' . $this->_order;
		// limit
		if ($this->_limit) $q[] = 'LIMIT ' . $this->_limit;
		// limit
		if ($this->_offset) $q[] = 'OFFSET ' . $this->_offset;
		//
		return join(' ', $q);
	}


	function from($table)
	{
		$this->_table = new Table($this, is_array($table) ? $table : func_get_args());
		return $this;
	}


	function cols($fields)
	{
		$fields = is_array($fields) ? $fields : func_get_args();
		$select = [];
		$i = 0;
		foreach ($fields as $_alias => $_field)
		{
			$select[$i] = is_a($_field, __NAMESPACE__ . '\Expression') ? (string)$_field : $this->quoteField($_field);
			if (!is_int($_alias)) $select[$i] .= ' AS ' . $this->quoteField($_alias);
			$i++;
		}
		$this->_select = $select;
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
						$this->_where[] = new Where($this, $_value);
					}
					else
					{
						throw new \Exception('This kind of array is not supported.');
					}
				}
				else
				{
					$this->_where[] = new Where($this, $_expr, $_value);
				}
			}
		}
		elseif (func_num_args()>=3)
		{
			$_where = new \ReflectionClass(__NAMESPACE__ . '\Where');
			$this->_where[] = $_where->newInstanceArgs(array_merge([$this], func_get_args()));
		}
		else
		{
			$this->_where[] = new Where($this, $expr, $value);
		}
		return $this;
	}
	
	function _W()
	{
		$w = [];
		foreach ($this->_where as $_w) $w[] = (string)$_w;
		return $w;
	}


	function order($order)
	{
		$order = join(', ', is_array($order) ? $order : func_get_args());
		preg_match_all('/([\w\.]+)\s*(asc|desc)?/i', $order, $m);
		foreach ($m[0] as $i => $_order)
		{
			$m[0][$i] = str_replace($m[1][$i], $this->quoteField($m[1][$i]), $m[0][$i]);
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


	function exec()
	{
		return $this->_query->exec(self::__toString());
	}


	function count()
	{
		$count = clone $this;
		$count->_select = 'COUNT(*)';
		return (int)$count->exec()->fetchColumn();
	}


	function all()
	{
		return $this->exec()->fetchAll();
	}


	function assoc($field='id')
	{
		$res = [];
		foreach ($this->all() as $row) $res[$row->{$field}] = $row;
		return $res;
	}


	function pairs($field1='id', $field2='name')
	{
		$pairs = clone $this;
		$pairs->cols($field1, $field2);
		return $pairs->exec()->fetchAll(\PDO::FETCH_COLUMN|\PDO::FETCH_UNIQUE);
	}
}