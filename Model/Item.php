<?php
/**
* 
*/
abstract class Lemmon_Model_Item
{
	protected $id;
	protected $table;
	
	private $_item;

	private $_connection;
	private $_mustReload = true;
	
	function __construct()
	{
		$this->_connection = Lemmon_Db_Connection::getInstance()->getNotorm();
		
		if (!$this->table)
		{
			$this->table = Lemmon_String::pl(Lemmon_String::classToTableName(get_class($this)));
		}
		
		$this->define();
	}
	
	abstract function define();
	
	function set($id)
	{
		$this->id = $id;
		return $this;
	}
	
	function __isset($key)
	{
		$this->reload();
		
		return isset($this->_item[$key]);
	}
	
	function __get($key)
	{
		$this->reload();
		
		return $this->_item[$key];
	}
	
	function reload($force=false)
	{
		if ($force or $this->_mustReload)
		{
			$this->_item = $this->_connection->{$this->table}[$this->id];
			$this->_mustReload = false;
		}
	}
}
