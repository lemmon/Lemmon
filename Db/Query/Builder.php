<?php

namespace Lemmon\Db\Query;

use Lemmon,
    Lemmon\Db\Connection,
    Lemmon\Db\Query;

/**
* 
*/
class Builder
{
	private $_connectino;
	private $_table;


	function __construct(Connection $connection, $table)
	{
		$this->_connection = $connection;
		$this->_table = $table;
	}
}
