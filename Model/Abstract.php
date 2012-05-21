<?php
/**
* 
*/
class Lemmon_Model_Abstract
{
	private static $_cache = array();

	protected $values = array();
	protected $data = array();

	protected $_name = 'name';
	protected $_primary = 'id';

	private $_id;

	function __construct($_item=null)
	{
		if ($_item)
		{
			if (is_numeric($_item))
			{
				// defined PRIMARY
				$this->_id = $_item;
				#self::$_cache[$_item] = $this;
			}
			elseif (is_string($_item))
			{
				// find by __NAME__
				throw new Exception('TODO');
			}
			elseif (is_object($_item))
			{
				// create from Object
				$this->_id = $_item->getPrimaryVal();
				unset($_item);
			}
			else
			{
				throw new Exception('TODO');
			}
		}
	}
	
	function __toString()
	{
		return (string)$this->values[$this->_id];
	}

	final public function make($id)
	{
		$class_name = get_called_class();
		return new $class_name($id);
		/*
		if (array_key_exists($id, self::$_cache))
		{
			dump('Cached!');
			return self::$_cache[$id];
		}
		else
		{
			$class_name = get_called_class();
			return new $class_name($id);
		}
		*/
	}
	
	final public function __isset($key)
	{
		return ($key==$this->_primary) or (true);
	}

	final public function __get($key)
	{
		if ($key==$this->_primary)
		{
			return $this->_id;
		}
		else
		{
			return $this->data[$this->_id][$key];
		}
	}
	
	final public function save()
	{
		// That's it
	}
	
	final public function getPrimaryVal()
	{
		return $this->_id;
	}
	
	final public static function collection()
	{
		if ($this)
		{
			return 'THIS';
		}
		else
		{
			$class = get_called_class();
			$model = new $class;
			return $model->values;
		}
	}
	
	final public function toArray()
	{
		return array(
			$this->_primary => $this->_id,
			$this->_name => $this->values[$this->_id],
		);
	}
	
	final public function all()
	{
		throw new Exception('TODO');
	}
	
	final public function first()
	{
		throw new Exception('TODO');
	}
	
	final public function find($id)
	{
		$this->_id = $id;
		return $this;
	}
}
