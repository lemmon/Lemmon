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
 * Model.
 */
abstract class AbstractRow
{
	static protected $model;

	protected $data = [];
	

	final function __construct($data=null)
	{
		#if (is_array())
		if (isset($data))
		{
			throw new \Exception('[todo]');
		}
	}


	static function find($cond)
	{
		if (!isset(self::$model))
		{
			throw new \Exception('No model has been set up.');
		}
		
		return call_user_func([self::$model, 'find'], $cond)->first();
	}


	function __get($key)
	{
		return $this->data[$key];
	}


	function __set($key, $val)
	{
		$this->data[$key] = $val;
	}
}
