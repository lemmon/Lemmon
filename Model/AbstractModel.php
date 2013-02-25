<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Model;

use \Lemmon\Db\Adapter as DbAdapter,
    \Lemmon\Sql\Statement as SqlStatement;

/**
 * Model.
 */
abstract class AbstractModel implements \IteratorAggregate, \ArrayAccess
{
	const FETCH_AS_ARRAY = 1;

	static $rowClass;
	static $table;
	static $primary = 'id';
	static $fields;
	static $uploads;
	static $sanitize;
	static $required;
	static $unique;
	static $timestamp;
	static $hasOne;
	static $hasMany;
	static $belongsTo;
	static $hasAndBelongsToMany;
	static $uploadDir;

	private $_query;
	private $_statement;
	private $_schema;

	private $_all;


	final function __construct()
	{
		$class_name = get_class($this);
		
		// adapter
		$this->_query = $query = DbAdapter::getDefault()->query();
		$this->_statement = $statement = new SqlStatement($query);
		
		// schema
		$this->_schema = $schema = Schema::factory($class_name);
		
		// table
		$statement->setTable($schema->table);
		
		// init model
		if (method_exists($this, '__init'))
		{
			$this->__init();
		}
	}


	function __call($method, $args)
	{
		if (method_exists($this->_statement, $method))
		{
			unset($this->_all);
			call_user_func_array([$this->_statement, $method], $args);
			return $this;
		}
		else
		{
			throw new \Exception(sprintf('Unknown method %s().', $method));
		}
	}


	static function find($cond = null)
	{
		$class_name = get_called_class();
		$model = new $class_name;

		if (func_num_args() > 1)
		{
			// many rows
			throw new \Exception('[todo] Many rows.');
		}
		elseif (is_int($cond) or is_string($cond))
		{
			// returns Row
			return $model->wherePrimary($cond);
		}
		elseif (is_array($cond))
		{
			// returns Row
			return $model->where($cond);
		}
		elseif (is_null($cond))
		{
			// returns Model
			return $model;
		}
		else
		{
			throw new \Exception(sprintf('Unknown condition type (%s).', gettype($cond)));
		}
	}


	function wherePrimary($id)
	{
		return $this->where([$this->_schema->primary[0] => $id]);
	}


	function create()
	{
		return new $this->_schema->rowClass;
	}


	function getIterator()
	{
		return new \ArrayIterator($this->all());
	}




	function offsetExists($i)
	{
		$all = ($this->_all) ?: ($this->_all = $this->all());
		return array_key_exists($i, $all);
	}
	
	function offsetGet($i)
	{
		$all = ($this->_all) ?: ($this->_all = $this->all());
		return $all[$i];
	}
	
	function offsetSet($offset, $value)
	{
		return false;
	}
	
	function offsetUnset($offset)
	{
		return false;
	}




	private function _getIterator()
	{
		$query = new \Lemmon\Sql\Select($this->_statement);
		$query->cols($this->_schema->table . '.*');
		$pdo_statement = $query->exec();
		$pdo_statement->setFetchMode(\PDO::FETCH_ASSOC);
		return $pdo_statement;
	}


	function count()
	{
		$query = new \Lemmon\Sql\Select($this->_statement);
		return $query->count();
	}


	function all()
	{
		$res = [];
		$rowClass = $this->_schema->rowClass;
		foreach ($this->_getIterator()->fetchAll() as $row)
		{
			$res[] = new $rowClass($row);
		}
		return $res;
	}


	function allByPrimary()
	{
		$res = [];
		$rowClass = $this->_schema->rowClass;
		foreach ($this->_getIterator()->fetchAll() as $row)
		{
			$res[$row['id']] = new $rowClass($row);
		}
		return $res;
	}


	function first()
	{
		$rowClass = $this->_schema->rowClass;
		if ($row = $this->_getIterator()->fetch())
		{
			return new $rowClass($row);
		}
	}
}
