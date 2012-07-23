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
		
		//
		return $query;
	}


	function all()
	{
		
	}


	function pairs()
	{
		
	}
}
