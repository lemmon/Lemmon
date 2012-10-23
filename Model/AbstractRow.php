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
    \Lemmon\Sql\Expression as SqlExpression,
    \Lemmon_I18N as I18n,
    \Lemmon\String as String;

/**
 * Model.
 */
abstract class AbstractRow
{
	static protected $model;

	protected $data = [];
	protected $dataPrev = [];

	private $_schema;


	final function __construct($data=null)
	{
		if (!isset(static::$model)) throw new \Exception('No model has been defined.');
	
		// model
		$this->_schema = Schema::factory(static::$model);
		
		// data
		if (isset($data))
		{
			throw new \Exception('[todo]');
		}
	}


	protected function onValidate(){}


	static function find($cond)
	{
		if (!isset(static::$model)) throw new \Exception('No model has been defined.');
		return call_user_func([static::$model, 'find'], $cond)->first();
	}


	private function _sanitize(&$f)
	{
		// timestamps
		if (is_array($ts = $this->_schema->get('timestamp')))
		{
			if (isset($ts[0]) and !isset($f[$ts[0]])) $f[$ts[0]] = new SqlExpression('NOW()');
			if (isset($ts[1])) $f[$ts[1]] = new SqlExpression('NOW()');
		}
		//
		return;
	}


	private function _validate(&$f)
	{
		// required fields
		if (is_array($r = $this->_schema->get('required')))
		{
			$fields = [];
			foreach ($r as $field => $condition)
			{
				switch ($condition)
				{
					case 'required':
						if (!isset($f[$field])) $fields[$field] = I18n::t(String::human($field));
						break;
					case 'allow_null':
						if (!array_key_exists($field, $f)) $fields[$field] = I18n::t(String::human($field));
						break;
					default:
						throw new \Exception(sprintf('Unknown flag `%s` on field `%s`.', $condition, $field));
						break;
				}
			}
			if ($fields)
			{
				throw new ValidationException(I18n::tn('Missing field %2$s', 'Missing %d fields (%s)', count($fields), join(', ', $fields)), array_keys($fields));
			}
		}
		// user defined validation
		if ($this->onValidate($f) === false)
		{
			throw new \ValidationException('?');
		}
		//
		return;
	}


	function save()
	{
		// data
		$data = $this->data;
		// validate
		if ($this->_sanitize($data) !== false and $this->_validate($data) !== false)
		{
			// query
			$q = new \Lemmon\Sql\Replace(DbAdapter::getDefault()->query(), $this->_schema->get('table'));
			// set values
			$q->set($data);
			// execute
			$q->exec();
		}
		//
		return $this;
	}


	function set($data)
	{
		$this->dataPrev += $this->data + array_intersect_key($this->data, $data);
		foreach ($data as $field => $value) self::__set($field, $value);
	}


	function __isset($key)
	{
		return array_key_exists($key, $this->data) || method_exists($this, $method = 'get' . $key);
	}


	function __get($key)
	{
		if (method_exists($this, $method = 'get' . $key))
		{
			return $this->{$method}();
		}
		else
		{
			return $this->data[$key];
		}
	}


	function __set($key, $val)
	{
		$this->data[$key] = $val;
	}
}
