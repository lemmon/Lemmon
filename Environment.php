<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
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
	static private $_development = false;


	/**
	 * Constructor.
	 */
	function __construct()
	{
		// defaults
		if ($_SERVER['SERVER_ADDR']=='127.0.0.1')
		{
			self::setDev(true);
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
	static function setDev($is=true)
	{
		self::$_development = (bool)$is;
	}


	/**
	 * Get development environment.
	 * @return bool
	 */
	static function isDev()
	{
		return self::$_development;
	}
}
