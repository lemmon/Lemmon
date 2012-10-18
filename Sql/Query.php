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

use \Lemmon\Db\Adapter as DbAdapter;

/**
 * SQL Query.
 */
class Query
{
	private $_adapter;
	#private $_statement;


	function __construct($query = null)
	{
		// adapter
		if (true/*is_null($adapter)*/)
		{
			$this->_adapter = DbAdapter::getDefault();
		}
		/* !todo
		elseif ($adapter instanceof DbAdapter)
		{
			$this->_adapter = $adapter;
		}
		else
		{
			throw new \Exception(sprintf('Unknown adapter type (%s).', gettype($adapter)));
		}
		*/
		
		// query
		if (isset($query))
		{
			if (is_string($query))
			{
				$statement = new Statement($this);
				$statement = call_user_func_array([$statement, 'setQuery'], func_get_args());
				$this->_statement = $statement;
			}
			else
			{
				throw new \Exception(sprintf('Unknown Query type (%s).', gettype($query)));
			}
		}
	}


	function select($table = null)
	{
		return new Select($this, is_array($table) ? $table : func_get_args());
	}


	function insert($table = null)
	{
		return new Insert($this, $table);
	}


	function replace($table = null)
	{
		return new Replace($this, $table);
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