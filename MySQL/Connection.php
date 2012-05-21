<?php
/**
 * Handles MySQL connections.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_MySQL_Connection
{
	public $host = 'localhost';
	public $user = '';
	public $password = '';
	public $db;
	public $charset = 'utf8';
	public $tablePrefix = '';
	
	private $_instanceName;
	private $_link;
	
	private static $_instances = array();
	private static $_currentInstanceName;
		
	public function __construct($instance_name=null)
	{
		// compute current instance name
		if (!$instance_name)
		{
			$instance_name = get_class($this);
		}
		
		// assign instances
		$this->_instanceName = $instance_name;
		self::$_instances[$instance_name] = $this;
		
		// connect if called as abstract class
		if (is_subclass_of($this, 'Lemmon_MySQL_Connection'))
		{
			$this->define();
			$this->connect();
		}
	}
	
	public static function getInstance($instance_name=null)
	{
		// compute current instance name
		if ($instance_name)
		{
			self::$_currentInstanceName = $instance_name;
		}
		else
		{
			if (is_subclass_of($this, 'Lemmon_MySQL_Connection'))
			{
				self::$_currentInstanceName = $instance_name = get_called_class();
			}
			else
			{
				return self::getCurrentInstance();
			}
		}
		
		// return instance if one exists
		if ($instance_name and self::$_instances[$instance_name])
		{
			return self::$_instances[$instance_name];
		}
		else
		{
			throw new Exception('No instance available');
		}
	}
	
	public static function getCurrentInstance()
	{
		if ($instance_name=self::$_currentInstanceName)
		{
			return self::$_instances[$instance_name];
		}
		else
		{
			throw new Exception('No instance available');
		}
	}
	
	public static function __callStatic($name, $arguments)
	{
		$query = Lemmon_MySQL_Query::withConnection($this);
		return call_user_func_array(array($query, $name), $arguments);
	}
	
	public static function q()
	{
		$query = Lemmon_MySQL_Query::withConnection($this);
		return call_user_func_array(array($query, 'query'), func_get_args());
	}
		
	public function getLink()
	{
		return $this->_link;
	}

	public function connect()
	{
		if ($link=@mysql_connect($this->host, $this->user, $this->password))
		{
			$this->_link = $link;
			if ($charset=$this->charset)
			{
				$this->charset($charset);
			}
			if ($db=$this->db)
			{
				$this->db($db);
			}
			self::$_currentInstanceName = $this->_instanceName;
			return $link;
		}
		else
		{
			throw new Exception('Unable to establish connection: ' . $this->_instance_name);
		}
	}
	
	public function charset($charset=null)
	{
		if ($charset)
		{
			$this->query('SET NAMES %s', $charset);
			return $this;
		}
		else
		{
			die('Query current charset.');
		}
	}
	
	public function db($db=null)
	{
		if ($db)
		{
			mysql_select_db($db, $this->_link);
			return $this;
		}
		else
		{
			die('Query current db.');
		}
	}

	public function query($query)
	{
		$query = Lemmon_MySQL_Query::withConnection($this);
		return call_user_func_array(array($query, 'query'), func_get_args());
	}
}
