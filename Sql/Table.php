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
 * SQL Table.
 */
class Table
{
	private $_name;
	private $_alias;
	private $_forceName;


	function __construct($table, $alias=null)
	{
		if (is_array($table))
		{
			$this->_name = current($table);
			$this->_alias = key($table);
		}
		else
		{
			$this->_name = $table;
			$this->_alias = $alias;
		}
	}


	function forceName($force = true)
	{
		$this->_forceName = $force;
		return $this;
	}


	function __toString()
	{
		return $this->toString();
	}


	function toString()
	{
		$res = Quote::field($this->_name);
		if ($alias = $this->_alias) $res .= ' AS ' . Quote::field($alias);
		return $res;
	}


	function getAlias()
	{
		return $this->_forceName ? $this->getAliasOrName() : $this->_alias;
	}


	function getAliasOrName()
	{
		return ($this->_alias) ?: $this->_name;
	}
}