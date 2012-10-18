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

/**
 * Model Schema.
 */
class Schema
{
	static private $_instances = [];

	private $_model;
	private $_schema;


	protected function __construct($model_name)
	{
		$s = [];
		// row class
		$s['rowClass'] = ($model_name::$rowClass) ?: \Lemmon\String::sg($model_name);
		// table
		$s['table'] = ($model_name::$table) ?: \Lemmon\String::classToTableName($model_name);
		//
		$s['primary'] = (array)$model_name::$primary;
		// fields
		# TODO
		// sanitize
		# TODO
		// required
		if ($required = $model_name::$required and is_array($required))
		{
			$r = [];
			foreach ($required as $key => $val)
			{
				if (is_int($key)) $r[$val] = 'required';
				else              $r[$key] = $val;
			}
			$s['required'] = $r;
		}
		// unique
		# TODO
		// timestmp
		if ($timestamp = $model_name::$timestmp and is_array($timestamp))
		{
			$s['timestamp'] = $timestamp;
		}
		// hasOne
		# TODO
		// hasMany
		# TODO
		// belongsTo
		# TODO
		// hasAndBelongsToMany
		# TODO
		//
		$this->_schema = $s;
	}


	static function factory($model_name)
	{
		if (array_key_exists($model_name, self::$_instances))
		{
			return self::$_instances[$model_name];
		}
		else
		{
			return self::$_instances[$model_name] = new self($model_name);
		}
	}


	function __get($key)
	{
		return $this->get($key);
	}


	function get($param)
	{
		return $this->_schema[$param];
	}
}
