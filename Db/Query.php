<?php

namespace Lemmon\Db;

/**
* 
*/
class Query
{
	private $_connection;
	private $_tablePrefix;
	private $_query;
	private $_queryRaw;
	private $_params = array();


	/**
	 * Constructor.
	 * @param Connection $connection
	 * @param string     $query
	 * @param array      $params
	 */
	function __construct(Connection $connection, $query, array $params=null)
	{
		$this->_connection = $connection;
		$this->_tablePrefix = $connection->getTablePrefix();
		$this->_queryRaw = $query;
		$this->_params = $params;
	}


	/**
	 * Returns query.
	 * @return string
	 */
	function __toString()
	{
		return $this->_query=$this->_parse($this->_queryRaw, $this->_params);
	}


	/**
	 * Parse query.
	 * @param  string  $query
	 * @param  array   $params
	 * @return string
	 */
	private function _parse($query, $params)
	{
		// parse table name
		$query = preg_replace(
			'/\[([\w-_]+)\]/u',
			'`' . $this->_tablePrefix . '$1`',
			$query);

		// match all the variables
		preg_match_all('/%_?\w{1,3}/', $query, $matches, PREG_OFFSET_CAPTURE);
		foreach (array_reverse($matches[0], true) as $i => $match)
		{
			switch ($match[0])
			{
				// string
				case '%s':
					$query = substr_replace($query, $params[$i]!==null ? '\'' . addslashes($params[$i]) . '\'' : 'NULL', $match[1], 2);
					break;
				// string LIKE % left
				case '%sll':
					$query = substr_replace($query, '\'%' . addslashes($params[$i]) . '\'', $match[1], 4);
					break;
				// string LIKE % right
				case '%slr':
					$query = substr_replace($query, '\'' . addslashes($params[$i]) . '%\'', $match[1], 4);
					break;
				// string LIKE % both sides
				case '%slb':
					$query = substr_replace($query, '\'%' . addslashes($params[$i]) . '%\'', $match[1], 4);
					break;
				// integer
				case '%i':
					$query = substr_replace($query, (int)($params[$i]), $match[1], 2);
					break;
				// float or decimal
				case '%d':
				case '%f':
					$query = substr_replace($query, (float)($params[$i]), $match[1], 2);
					break;
				// field
				case '%n':
					$query = substr_replace($query, '`' . $params[$i] . '`', $match[1], 2);
					break;
				// raw
				case '%r':
					$query = substr_replace($query, $params[$i], $match[1], 2);
					break;
				// table
				case '%t':
					$query = substr_replace($query, '`' . ($table_prefix . $params[$i]) . '`', $match[1], 2);
					break;
			}
		}
		
		//
		return $query;
	}
}
