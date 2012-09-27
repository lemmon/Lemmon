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
 * SQL Table.
 */
class Table
{
	private $_statement;
	private $_table;


	function __construct(QueryStatement $statement, $table)
	{
		$this->_statement = $statement;
		
		if (is_string($table))
		{
			$this->_table = [$table];
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
				$table[$_alias] = $this->_statement->quoteField($_table);
			else
				$table[$_alias] = $this->_statement->quoteField($_table) . ' AS ' . $this->_statement->quoteField($_alias);
		}
		return join(', ', $table);
	}
}