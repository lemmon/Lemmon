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
 * SQL Query.
 */
class Query
{
	private $_adapter;


	function __construct($adapter)
	{
		$this->_adapter = $adapter;
	}


	function select($table=null)
	{
		return new Select($this, is_array($table) ? $table : func_get_args());
	}


	function getAdapter()
	{
		return $this->_adapter;
	}


	function exec($query)
	{
		return $this->_adapter->getPdo()->query($query);
	}
}