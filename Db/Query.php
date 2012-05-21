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
	
	function __construct(Connection $connection, $query, array $params=null)
	{
		$this->_connection = $connection;
		$this->_tablePrefix = $connection->getTablePrefix();
	}
}
