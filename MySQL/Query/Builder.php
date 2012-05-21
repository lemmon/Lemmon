<?php
/**
* 
*/
class Lemmon_MySQL_Query_Builder extends Lemmon_MySQL_Query
{
	protected $_table;
	protected $_primary = 'id';
	protected $_select;
 	protected $_sort;
	
	private $_set;
	private $_join;
	private $_find;
	private $_group;
	private $_offset;
	private $_limit;
	
	private $_timeStampable;
	
	public function __construct($table, $connection=null)
	{
		parent::__construct($connection);
		
		if (!$this->_table)
		{
			$this->_table = $table;
		}
	}
	
	final public function from($table, $connection=null)
	{
		new self($table, $connection);
	}
	
	final public function getTable()
	{
		return $this->_table;
	}
	
	final public function getPrimary()
	{
		return $this->_primary;
	}
	
	final public function select($foo)
	{
		foreach (func_get_args() as $field)
		{
			$select[] = is_array($field) ? call_user_func_array(array($this, 'parse'), $field) : $this->parse('$_table.%n', $field);
		}
		$this->_select = $select;
		return $this;
	}
	
	final public function selectRaw($select)
	{
		$this->_select = func_get_args();
		return $this;
	}
	
	final public function selectAll()
	{
		$this->_select = null;
		return $this;
	}
	
	private function _findPush($query)
	{
		$this->_find[md5($query)] = $query;
	}
	
	final public function findByPrimary($id)
	{
		$query = $this->parse('%t.%n=%i', $this->getTable(), $this->getPrimary(), $id);
		$this->_find = array('PRIMARY' => $query);
	}
	
	final public function find($id)
	{
		if (func_num_args()==1)
		{
			self::findByPrimary($id);
		}
		else
		{
			$args = array_chunk(func_get_args(), 2);
			foreach ($args as $i => $chunk)
			{
				if ($chunk[1]===null)
				{
					$args[$i] = $this->parse('%t.%n IS NULL', $this->getTable(), $chunk[0]);
				}
				elseif (is_array($chunk[1]))
				{
					$args[$i] = $this->parse('%t.%n IN (%_as)', $this->getTable(), $chunk[0], $chunk[1]);
				}
				else
				{
					$args[$i] = $this->parse('%t.%n=%s', $this->getTable(), $chunk[0], $chunk[1]);
				}
			}
			$this->_findPush(join(' OR ', $args));
		}
		return $this;
	}
	
	final public function exclude($id)
	{
		if (func_num_args()==1)
		{
			if ($id!==null) $this->_findPush( $this->parse('%t.%n!=%s', $this->getTable(), $this->getPrimary(), $id) );
			else            $this->_findPush( $this->parse('%t.%n IS NOT NULL', $this->getTable(), $this->getPrimary()) );
		}
		else
		{
			$args = array_chunk(func_get_args(), 2);
			foreach ($args as $i => $chunk)
			{
				$args[$i] = $this->parse('%t.%n!=%s', $this->getTable(), $chunk[0], $chunk[1]);
			}
			$this->_findPush(join(' OR ', $args));
		}
		return $this;
	}
	
	final public function findNull($field)
	{
		$this->_findPush( $this->parse('%t.%n IS NULL', $this->getTable(), $field) );
		return $this;
	}
	
	final public function excludeNull($field)
	{
		$this->_findPush( $this->parse('%t.%n IS NOT NULL', $this->getTable(), $field) );
		return $this;
	}
	
	final public function findLike($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n LIKE %s', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findLikeR($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n LIKE %slr', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findLikeL($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n LIKE %slr', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findLikeB($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n LIKE %slb', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findLt($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n<%s', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findLte($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n<=%s', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findGt($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n>%s', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findGte($a, $b)
	{
		$this->_findPush( $this->parse('%t.%n>=%s', $this->getTable(), $a, $b) );
		return $this;
	}
	
	final public function findQuery($query)
	{
		$this->_findPush( call_user_func_array(array($this, 'parse'), func_get_args()) );
		return $this;
	}
	
	final public function group($field)
	{
		$this->_group = $field;
		return $this;
	}
	
	final public function groupByPrimary()
	{
		if (is_array($primary=$this->getPrimary()))
		{
			throw new Exception('[TODO] PRIMARY is an Array()');
		}
		else
		{
			$this->_group = $this->parse('$_table.%n', $primary);
		}
	}
	
	final public function sort($sort)
	{
		$this->_sort = call_user_func_array(array($this, 'parse'), func_get_args());
		return $this;
	}
	
	final public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}
	
	final public function offset($offset, $limit=null)
	{
		$this->_offset = $offset;
		if ($limit) $this->_limit = $limit;
		return $this;
	}
	
	final public function join($table, $on=array())
	{
		foreach ($on as $key => $val)
		{
			if (is_array($val))
			{
				if (is_numeric($key))
					$query = call_user_func_array(array($this, 'parse'), $val);
				else
					$query = $this->parse('%t.%n=', $table, $key) . call_user_func_array(array($this, 'parse'), $val);
			}
			elseif ($val{0}=='$')
				$query = $this->parse('%t.%n=%t.%n', $table, $key, $this->getTable(), substr($val, 1));
			else
				$query = $this->parse('%t.%n=%s', $table, $key, $val);

			$this->_join[$table][md5($query)] = $query;
		}
		return $this;
	}
	
	final public function set($key, $val)
	{
		$this->_set[$key] = $val;
		return $this;
	}
	
	final public function setQuery($query)
	{
		$this->_set[] = func_get_args();
		return $this;
	}
	
	final public function timeStampable($created_at=null, $updated_at=null)
	{
		if ($created_at or $updated_at)
		{
			if ($created_at) $this->_timeStampable['created_at'] = $created_at;
			if ($updated_at) $this->_timeStampable['updated_at'] = $updated_at;
		}
		else
		{
			$this->_timeStampable = array(
				'created_at' => 'created_at',
				'updated_at' => 'updated_at',
			);
		}
	}

	final public function paginate($page, &$paginate, $perpage=25, $range=3)
	{
		$n = $this->count();
		$pages = ceil($n/$perpage);
		$paginate = array(
			'page' => $page,
			'pages' => $pages,
			'perpage' => $perpage,
			'total' => $n,
			'page_min' => 0,
			'page_max' => $pages-1,
		);
		if ($range)
		{
			$page_min = $page-$range;
			$page_max = $page+$range;
			if ($page_min<0)
			{
				$page_max -= $page_min;
				$page_min = 0;
			}
			if ($page_max>$pages-1)
			{
				$page_min -= $page_max-$pages+1;
				$page_max = $pages-1;
			}
			if ($page_min<$range)
			{
				$page_min = 0;
			}
			if ($page_max>$pages-$range-1)
			{
				$page_max = $pages-1;
			}
			$paginate['page_min'] = $page_min;
			$paginate['page_max'] = $page_max;
		}
		$this->offset($page * $perpage, $perpage);
 		return $this->all();
	}

	final protected function prepareResult($type='select', $field=null)
	{
		if (!$this->getRes())
		{
			$this->_select($type, $field);
			$this->exec();
		}
	}
	
	private function _select($type='select', $field=null)
	{
		$this->clear();
		
		// SELECT
		switch ($type)
		{

			case 'select':
				if ($select=$this->_select)
					$this->push('SELECT ' . join(', ', $select));
				else
					$this->push('SELECT $_table.*');
				break;
				
			case 'distinct':
				$this->push('SELECT DISTINCT $_table.%n', $field);
				break;

			case 'count':
				if (is_array($primary=$this->getPrimary()))
				{
					foreach ($primary as $i => $val) $primary[$i] = array('$_table.%n', $val);
					$this->push('SELECT COUNT(%_and) AS `count`', $primary);
				}
				else
				{
					$this->push('SELECT COUNT($_table.%n) AS `count`', $primary);
				}
				break;

			case 'sum':
				$this->push('SELECT SUM($_table.%n) AS `sum`', $field);
				break;
				
		}
		
		// FROM
		$this->push('FROM $_table');
		
		// JOIN
		if ($join=$this->_join)
		{
			foreach ($join as $table => $on)
			{
				$this->push('INNER JOIN %t ON ' . join(' AND ', $on), $table);
			}
		}
		
		// WHERE
		$this->_conditions();
		
		// GROUP BY
		if ($group=$this->_group)
		{
			$this->push('GROUP BY %r', $group);
		}
		
		// ORDER BY
		if ($sort=$this->_sort and $type!='count')
		{
			$this->push('ORDER BY %r', $sort);
		}
		
		// LIMIT
		$this->_offset();
	}
	
	final public function count()
	{
		$this->prepareResult('count');
		$row = self::fetch();
		$this->freeRes();
		return $row->count;
	}
	
	final public function sum($field)
	{
		$this->prepareResult('sum', $field);
		$row = self::fetch();
		$this->freeRes();
		return $row->sum;
	}
	
	private function _assignTimeStampables(&$row, $at='create')
	{
		$now = date('Y-m-d H:i:s');
		if ($created_at=$this->_timeStampable['created_at'] and $at=='create') $row[$created_at] = $now;
		elseif ($created_at=$this->_timeStampable['created_at'] and $at=='replace') $row[$created_at] = $this->{$created_at};
		if ($updated_at=$this->_timeStampable['updated_at']) $row[$updated_at] = $now;
	}
	
	public function create($row=null)
	{
		if (!$row) $row = (array)$this->_set; elseif (is_object($row)) $row = (array)$row;
		$this->_assignTimeStampables($row);
		$this->clear();
		$this->push('INSERT INTO $_table');
		$this->push('%_v', $row);
		$this->exec();
		$this->freeRes();
		return $this;
	}
	
	public function update($row=null)
	{
		if (!$row) $row = (array)$this->_set; elseif (is_object($row)) $row = (array)$row;
		$this->_assignTimeStampables($row, 'update');
		$this->clear();
		$this->push('UPDATE $_table');
		$this->push('SET %_a', $row);
		$this->_conditions();
		$this->_offset();
		$this->exec();
		return $this;
	}
	
	public function createOrUpdate($row_create=null, $row_update=null)
	{
		if (!$row_create) $row_create = (array)$this->_set;
		if (!$row_update) $row_update = $row_create;
		$this->_assignTimeStampables($row_create);
		$this->_assignTimeStampables($row_update, 'update');
		$this->clear();
		$this->push('INSERT INTO $_table');
		$this->push('%_v', $row_create);
		$this->push('ON DUPLICATE KEY UPDATE %_a', $row_update);
		$this->exec();
		return $this;
	}
	
	public function replace($row=null)
	{
		if (!$row) $row = (array)$this->_set; elseif (is_object($row)) $row = (array)$row;
		$this->_assignTimeStampables($row, 'replace');
		$this->clear();
		$this->push('REPLACE INTO $_table');
		$this->push('%_v', $row);
		$this->exec();
		return $this;
	}
	
	public function delete($where=null)
	{
		if ($where)
		{
			$this->_findPush($this->parse('%_and', $where));
		}
		$this->clear();
		$this->push('DELETE FROM $_table');
		$this->_conditions();
		$this->_limit();
		$this->exec();
		return $this;
	}
	
	private function _conditions()
	{
		if ($where=$this->_find)
		{
			$this->push('WHERE ' . join(' AND ', $where), false);
		}
	}
	
	private function _limit()
	{
		if ($limit=$this->_limit)
		{
			$this->push('LIMIT %i', $limit);
		}
	}
	
	private function _offset()
	{
		if ($limit=$this->_limit)
		{
			$this->push('LIMIT %i,%i', $this->_offset, $limit);
		}
	}
}
