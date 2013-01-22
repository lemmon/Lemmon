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
	static private $_defaultUploadDir = 'uploads';

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
		// uploads
		if ($uploads = $model_name::$uploads)
		{
			$u = [];
			foreach ($uploads as $key => $val)
			{
				if (is_int($key)) $u[$val] = trim($model_name::$uploadDir, '/');
				else              $u[$key] = trim($model_name::$uploadDir, '/') . '/' . trim($val, '/');
			}
			$s['uploads'] = $u;
		}
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
				if (is_int($key))
				{
					if ($s['uploads'][$val]) $r[$val] = 'upload';
					else                     $r[$val] = 'required';
				}
				else              $r[$key] = $val;
			}
			$s['required'] = $r;
		}
		// unique
		# TODO
		// timestamp
		if ($timestamp = $model_name::$timestamp and is_array($timestamp))
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
		// upload dir
		$_path = [];
		if ($_dir = self::$_defaultUploadDir)
		{
			if ($_dir{0} != '/') $_path[] = ROOT_DIR;
			$_path[] = $_dir;
		}
		$s['uploadDir'] = join('/', $_path);
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


	static function setDefaultUploadDir($dir)
	{
		self::$_defaultUploadDir = rtrim($dir, '/');
	}


	static function getDefaultUploadDir()
	{
		return self::$_defaultUploadDir{0} == '/' ? self::$_defaultUploadDir : ROOT_DIR . '/' . self::$_defaultUploadDir;
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
