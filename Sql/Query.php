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
	private $_statement;


	function __construct($query = null, $adapter = null)
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
			throw new \Exception('Not this way.');
			if (is_string($query))
			{
				dump(func_get_args());die('--6');
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


	function update($table = null)
	{
		return new Update($this, $table);
	}


	function replace($table = null)
	{
		return new Replace($this, $table);
	}


	function delete($table = null)
	{
		return new Delete($this, $table);
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