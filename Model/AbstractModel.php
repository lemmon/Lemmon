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
abstract class AbstractModel implements \IteratorAggregate
{
	const FETCH_AS_ARRAY = 1;

	static $rowClass;
	static $table;
	static $primary = 'id';
	static $fields;
	static $sanitize;
	static $required;
	static $unique;
	static $timestmp;
	static $hasOne;
	static $hasMany;
	static $belongsTo;
	static $hasAndBelongsToMany;

	private $_query;
	private $_statement;
	private $_schema;


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
			call_user_func_array([$this->_statement, $method], $args);
			return $this;
		}
		else
		{
			throw new \Exception(sprintf('Unknown method %s().', $method));
		}
	}


	static function find($cond=null)
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


	function getIterator($flags = null)
	{
		return new \ArrayIterator($this->all());
	}


	private function _getIterator($flags = null)
	{
		$query = new \Lemmon\Sql\Select($this->_statement);
		$query->cols($this->_schema->table . '.*');
		$pdo_statement = $query->exec();
		// fetch into row
		/*
		if ($rowClass = $this->_schema->rowClass and !($flags & self::FETCH_AS_ARRAY))
		{
			$pdo_statement->setFetchMode(\PDO::FETCH_CLASS, $rowClass);
		}
		*/
		$pdo_statement->setFetchMode(\PDO::FETCH_ASSOC);
		//
		return $pdo_statement;
	}


	function count()
	{
		$query = new \Lemmon\Sql\Select($this->_statement);
		return $query->count();
	}


	function all($flags = null)
	{
		$res = [];
		$rowClass = $this->_schema->rowClass;
		foreach ($this->_getIterator($flags)->fetchAll() as $row)
		{
			$res[] = new $rowClass($row);
		}
		return $res;
	}


	function first($flags = null)
	{
		$rowClass = $this->_schema->rowClass;
		if ($row = $this->_getIterator($flags)->fetch())
		{
			return new $rowClass($row);
		}
	}
}
