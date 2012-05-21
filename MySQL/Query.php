<?php
/**
 * General MySQL query object.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_MySQL_Query
{
	private $_connection;
	private $_query;
	private $_res;
	private $_affected;
	private $_insertId;
	
	private $_queriesCount = 0;
	static private $_queriesCountTotal = 0;
	
	public function __construct($connection=null)
	{
		if (is_string($connection))
		{
			$this->_connection = Lemmon_MySQL_Connection::getInstance($connection);
		}
		elseif ($connection instanceof Lemmon_MySQL_Connection)
		{
			$this->_connection = $connection;
		}
		elseif (!$connection)
		{
			$this->_connection = Lemmon_MySQL_Connection::getCurrentInstance();
		}
		else
		{
			throw new Lemmon_Exception('Couldn\'t recognize this type of connection');
		}
	}
	
	final public static function withConnection($connection)
	{
		return new Lemmon_MySQL_Query($connection);
	}
	
	final public function connection($connection=null)
	{
		if ($connection)
		{
			$this->_connection = $connection;
			return $this;
		}
		else
		{
			return $this->_connection;
		}
	}
	
	public function getTable()
	{
		return '';
	}
	
	final public function parse($query)
	{
		$params = func_get_args();
		array_shift($params);
		
		$table_prefix = (string)$this->_connection->tablePrefix;
		
		$query = preg_replace('/\[([\w-_]+)\]/u', '`' . $table_prefix . '$1`', $query);
		$query = str_replace('$_table', $table=$this->getTable() ? '`' . $table_prefix . $this->getTable() . '`' : '', $query);

		preg_match_all('/%_?\w{1,3}/', $query, $matches, PREG_OFFSET_CAPTURE);
		foreach (array_reverse($matches[0], true) as $i => $match)
		{
			switch ($match[0])
			{
				case '%s':
					$query = substr_replace($query, $params[$i]!==null ? '\'' . addslashes($params[$i]) . '\'' : 'NULL', $match[1], 2);
					break;
				case '%sll':
					$query = substr_replace($query, '\'%' . addslashes($params[$i]) . '\'', $match[1], 4);
					break;
				case '%slr':
					$query = substr_replace($query, '\'' . addslashes($params[$i]) . '%\'', $match[1], 4);
					break;
				case '%slb':
					$query = substr_replace($query, '\'%' . addslashes($params[$i]) . '%\'', $match[1], 4);
					break;
				case '%i':
					$query = substr_replace($query, (int)($params[$i]), $match[1], 2);
					break;
				case '%d':
				case '%f':
					$query = substr_replace($query, (float)($params[$i]), $match[1], 2);
					break;
				case '%n':
					$query = substr_replace($query, '`' . $params[$i] . '`', $match[1], 2);
					break;
				case '%r':
					$query = substr_replace($query, $params[$i], $match[1], 2);
					break;
				case '%t':
					$query = substr_replace($query, '`' . ($table_prefix . $params[$i]) . '`', $match[1], 2);
					break;
				case '%_f':
					$query = substr_replace($query, call_user_func_array('self::parse', $params[$i]), $match[1], 3);
					break;
				case '%_and':
					$query = substr_replace($query, join(' AND ', self::_parsePartialArray($params[$i])), $match[1], 5);
					break;
				case '%_or':
					$query = substr_replace($query, join(' OR ', self::_parsePartialArray($params[$i])), $match[1], 4);
					break;
				case '%_as':
					$query = substr_replace($query, join(', ', self::_parsePartialArrayOfStrings($params[$i])), $match[1], 4);
					break;
				case '%_a':
					$query = substr_replace($query, join(', ', self::_parsePartialArray($params[$i])), $match[1], 3);
					break;
				case '%_v':
					$_fields = $_values = array();
					foreach ((array)$params[$i] as $key => $val) /*if (strlen($val))*/
					{
						$_fields[$key] = self::parse('%n', $key);
						$_values[$key] = self::parse('%s', $val);
					}
					$query = substr_replace($query, '(' . join(', ', $_fields) . ') VALUES (' . join(', ', $_values) . ')', $match[1], 3);
					break;
			}
		}
		
		return $query;
	}
	
	private function _parsePartialArray($params)
	{
		$params = (array)$params;
		foreach ($params as $i => $_params)
		{
			if (is_int($i))
			{
				if (is_array($_params))
				{
					$params[$i] = call_user_func_array('self::parse', $_params);
				}
				else
				{
					$params[$i] = self::parse('%r', $_params);
				}
			}
			else
			{
				$params[$i] = self::parse('%n=%s', $i, $_params);
			}
		}
		return $params;
	}
	
	private function _parsePartialArrayOfStrings($params)
	{
		$params = (array)$params;
		foreach ($params as $key => $val)
		{
			if (is_int($key))
			{
				$params[$key] = self::parse('%s', $val);
			}
			else
			{
				$params[$key] = self::parse('%n=%s', $key, $val);
			}
		}
		return $params;
	}
	
	final public function query($query)
	{
		$t -= microtime(true);
		$this->_query = $query = call_user_func_array('self::parse', func_get_args());
		$this->_setRes($res=mysql_query($query, $this->_connection->getLink()));
		$this->_affected = mysql_affected_rows();
		$this->_insertId = mysql_insert_id();
		$t += microtime(true);
		
		$this->_queriesCount++;
		self::$_queriesCountTotal++;
		Lemmon_Logger::mysqlQuery($query, $t);
		
		if ($error=mysql_error())
		{
			throw new Lemmon_MySQL_Error($error);
		}
		else
		{
			return $this;
		}
	}
	
	final public function getQueriesCount()
	{
		return $this->_queriesCount;
	}
	
	final public static function getTotalQueriesCount()
	{
		return self::$_queriesCountTotal;
	}
	
	final public function clear()
	{
		$this->_query = null;
	}
	
	final public function push($query, $parse=null)
	{
		if (!is_array($this->_query)) $this->clear();
		$this->_query[] = $parse!==false ? call_user_func_array('self::parse', func_get_args()) : $query;
		return $this;
	}
	
	final public function exec($query=null)
	{
		if ($query)
		{
			call_user_func_array('self::query', func_get_args());
			return $this->_res;
		}
		else
		{
			$t -= microtime(true);
			$query = $this->_query;
			if (is_array($query))
			{
				$query = join("\n", $query);
			}
			$this->_setRes($res=mysql_query($query, $this->_connection->getLink()));
			$this->_affected = mysql_affected_rows();
			$this->_insertId = mysql_insert_id();
			$t += microtime(true);

			$this->_queriesCount++;
			self::$_queriesCountTotal++;
			Lemmon_Logger::mysqlQuery($query, $t);

			if ($error=mysql_error())
			{
				throw new Lemmon_Exception($error);
			}
			else
			{
				return $res;
			}
		}
	}
	
	public function getInsertId()
	{
		return $this->_insertId;
	}
	
	public function getAffected()
	{
		return $this->_affected;
	}
	
	public function getRes()
	{
		return $this->_res;
	}
	
	private function _setRes($res)
	{
		if (gettype($res)=='resource') $this->_res = $res;
	}
	
	protected function prepareResult() { }

	final public function freeRes()
	{
		if ($this->_res)
		{
			mysql_free_result($this->_res);
			$this->_res = null;
		}
		return $this;
	}
	
	public function fetch()
	{
		return mysql_fetch_object($this->getRes());
	}
	
	public function first($object=true)
	{
		$this->prepareResult();
		$row = $this->fetch($object);
		$this->freeRes();
		return $row;
	}
	
	final public function pairs($field1=null, $field2=null)
	{
		if ($this instanceof Lemmon_Model)
		{
			if (!$field1) $field1 = $this->_primary;
			if (!$field2) $field2 = $this->_name;
		}
		
		$data = array();
		$this->prepareResult();
		while ($row=$this->fetch(true)) $data[ $row->{$field1} ] = $row->{$field2};
		$this->freeRes();
		return $data;
	}
	
	final public function distinct($field)
	{
		$data = array();
		$this->prepareResult('distinct', $field);
		while ($row=$this->fetch(true)) $data[] = $row->{$field};
		$this->freeRes();
		return $data;
	}
	
	final public function all()
	{
		if (!$this and $class_name=get_called_class())
		{
			$class = new $class_name();
			return call_user_func_array(array($class, 'all'), func_get_args());
		}
		
		$data = array();
		$this->prepareResult();
		while ($row=$this->fetch(true)) $data[] = $row;
		$this->freeRes();
		return $data;
	}
	
	final public function allByAssoc($assoc)
	{
		if (!$this and $class_name=get_called_class())
		{
			$class = new $class_name();
			return call_user_func_array(array($class, 'all'), func_get_args());
		}
		
		$data = array();
		$this->prepareResult();
		while ($row=$this->fetch(true)) $data[ $row->{$assoc} ] = $row;
		$this->freeRes();
		return $data;
	}
}
