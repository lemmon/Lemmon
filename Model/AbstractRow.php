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
	protected $data = [];
	
	private $_model;


	final function __construct($data=null)
	{
		#if (is_array())
		if (isset($data))
		{
			throw new \Exception('[todo]');
		}
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
