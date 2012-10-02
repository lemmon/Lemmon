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


	private $_query;
	private $_statement;

	protected $table;
	protected $rowClass;
	protected $primary             = 'id';
	protected $required            = [];
	protected $unique              = [];
	protected $timestamp           = [];
	protected $hasOne              = [];
	protected $hasMany             = [];
	protected $belongsTo           = [];
	protected $hasAndBelongsToMany = [];

	private $_restrict = [];


	final function __construct()
	{
		$class_name = get_class($this);
		
		// adapter
		$this->_query = $query = DbAdapter::getDefault()->query();
		$this->_statement = $statement = new SqlStatement($query);
		
		// table
		if (!isset($this->table))
		{
			$this->table = \Lemmon\String::classToTableName($class_name);
		}
		$statement->from($this->table);
		
		// primary key
		if (!isset($this->primary))
		{
			throw new \Exception('[todo] Automatic primary key.');
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
			return $model->where([$model->primary => $cond]);
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


	function getIterator($flags=null)
	{
		$pdo_statement = (new \Lemmon\Sql\Select($this->_statement))->exec();
		// fetch into row
		if (isset($this->rowClass) and !($flags & self::FETCH_AS_ARRAY))
		{
			$pdo_statement->setFetchMode(\PDO::FETCH_CLASS, $this->rowClass);
		}
		//
		return $pdo_statement;
	}


	function all($flags=null)
	{
		return $this->getIterator($flags)->fetchAll();
	}


	function first($flags=null)
	{
		return $this->getIterator($flags)->fetch();
	}
}
