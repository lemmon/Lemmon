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
class Select extends AbstractStatement
{
	protected $_select = '*';


	function getQueryString()
	{
		$q = [];
		// select
		$q[] = 'SELECT ' . (is_array($this->_select) ? join(', ', $this->_select) : $this->_select);
		// from
		$q[] = 'FROM ' . $this->_table;
		// join
		if ($join = $this->_join) foreach ($join as $_join) $q[] = $_join->toString();
		// where
		if ($this->_where) $q[] = 'WHERE ' . join(' AND ', $this->_where);
		// gropu
		if ($this->_group) $q[] = 'GROUP BY ' . $this->_group;
		// order
		if ($this->_order) $q[] = 'ORDER BY ' . $this->_order;
		// limit
		if ($this->_limit) $q[] = 'LIMIT ' . $this->_limit;
		// limit
		if ($this->_offset) $q[] = 'OFFSET ' . $this->_offset;
		//
		return join(' ', $q);
	}


	function from($table, $alias=null)
	{
		$this->setTable($table, $alias);
		return $this;
	}


	function cols($fields)
	{
		$fields = is_array($fields) ? $fields : func_get_args();
		$select = [];
		$i = 0;
		foreach ($fields as $_alias => $_field)
		{
			$select[$i] = ($_field instanceof Expression) ? (string)$_field : Quote::field($_field);
			if (!is_int($_alias)) $select[$i] .= ' AS ' . Quote::field($_alias);
			$i++;
		}
		$this->_select = $select;
		return $this;
	}


	function count()
	{
		$count = clone $this;
		$count->_select = 'COUNT(*)';
		return (int)$count->exec()->fetchColumn();
	}


	function first()
	{
		return $this->exec()->fetch();
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


	function distinct($field)
	{
		$pairs = clone $this;
		$pairs->cols($field);
		return array_keys($pairs->exec()->fetchAll(\PDO::FETCH_UNIQUE));
	}
}