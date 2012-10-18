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
 * SQL Insert.
 */
class Insert extends AbstractStatement
{
	protected $_values = [];
	protected $_insertId;


	function getQueryString()
	{
		$q = [];
		// insert
		$q[] = 'INSERT';
		// from
		$q[] = 'INTO ' . $this->_table;
		// fields
		$q[] = '(`' . join('`, `', Quote::_field(array_keys($this->_values))) . '`)';
		// values
		$values = $this->_values;
		foreach ($values as $i => $value)
		{
			if (!($value instanceof Expression))
			{
				$values[$i] = Quote::value($value);
			}
		}
		$q[] = 'VALUES (' . join(', ', $values) . ')';
		//
		return join(' ', $q);
	}


	function setTable($table)
	{
		if (!is_string($table)) throw new \Exception('Only single table is allowed on Insert query.');
		parent::setTable($table);
	}


	function set($field, $value=false)
	{
		if (is_array($field))
		{
			foreach ($field as $_field => $_value) $this->_set($_field, $_value);
		}
		else
		{
			$this->_set($field, $value);
		}
	}


	function _set($field, $value)
	{
		if (is_array($value)) throw new \Exception('Array is not allowed on inserted value.');
		$this->_values[$field] = $value;
	}


	function exec()
	{
		$res = parent::exec();
		$this->_insertId = $this->_query->getAdapter()->getPdo()->lastInsertId();
		return $this;
	}
}