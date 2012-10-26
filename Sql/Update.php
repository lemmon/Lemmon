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
 * SQL Replace.
 */
class Update extends AbstractStatement
{
	protected $_values = [];


	function getQueryString()
	{
		$q = [];
		// replace
		$q[] = 'UPDATE';
		// from
		$q[] = $this->_table->toString();
		// fields & values
		foreach ($this->_values as $field => $value)
		{
			$values[] = Quote::field($field) . ' = ' . ($value instanceof Expression ? $value : Quote::value($value));
		}
		$q[] = 'SET ' . join(', ', $values);
		// where
		if ($this->_where) $q[] = 'WHERE ' . join(' AND ', $this->_where);
		// order
		if ($this->_order) $q[] = 'ORDER BY ' . $this->_order;
		// limit
		if ($this->_limit) $q[] = 'LIMIT ' . $this->_limit;
		//
		return join(' ', $q);
	}


	function setTable($table)
	{
		if (!is_string($table)) throw new \Exception('Only single table is allowed on Update query at this time.');
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
		if (is_array($value)) throw new \Exception('Array is not allowed on replaced value.');
		$this->_values[$field] = $value;
	}


	function exec()
	{
		$res = parent::exec();
		return $this;
	}
}