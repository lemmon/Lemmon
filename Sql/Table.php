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
	private $_table;


	function __construct($table)
	{
		if (is_string($table))
		{
			$this->_table = func_get_args();
		}
		elseif (is_array($table))
		{
			$this->_table = $table;
		}
		else
		{
			throw new \Exception('[todo] Unknown $table type.');
		}
	}


	function __toString()
	{
		$table = $this->_table;
		foreach ($table as $_alias => $_table)
		{
			if (is_int($_alias))
				$table[$_alias] = Quote::field($_table);
			else
				$table[$_alias] = Quote::field($_table) . ' AS ' . Quote::field($_alias);
		}
		return join(', ', $table);
	}
}