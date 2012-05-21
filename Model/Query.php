<?php
/**
* 
*/
abstract class Lemmon_Model_Query
{
	protected $table;

	private $_connection;
	private $_query;
	
	function __construct()
	{
		$this->_connection = Lemmon_Db_Connection::getInstance()->getNotorm();
		
		if (!$this->table)
		{
			$this->table = Lemmon_String::classToTableName(get_class($this));
		}
		
		$this->_query = $this->_connection->{$this->table}();
		
		$this->define();
	}
	
	abstract function define();
}
