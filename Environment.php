<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon;

/**
 * Handles environments.
 * 
 * @author Jakub Pelák <jpelak@gmail.com>
 */
class Environment
{
	static private $_development = null;


	/**
	 * Constructor.
	 */
	function __construct()
	{
		// defaults
		if (is_null(self::$_development))
		{
			if ($_SERVER['SERVER_ADDR']=='127.0.0.1')
			{
				self::setDev(true);
			}
		}
		// init class
		if (method_exists($this, '__init'))
		{
			$this->__init();
		}
	}


	/**
	 * Set development environment.
	 */
	static function setDev($is = true)
	{
		self::$_development = (bool)$is;
	}


	/**
	 * Get development environment.
	 * @return bool
	 */
	static function isDev()
	{
		return (bool)self::$_development;
	}
}
