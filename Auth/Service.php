<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Auth;

/**
 * Handles authentication.
 */
class Service
{
	/** @var string salt prefix configuration */
	private $_saltPrefix = '$2x$08$';

	/** @var int salt length */
	private $_saltLength = 22;

	/** @var string itoa64 */
	private $_itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';


	/**
	 * Generate salt.
	 * @return string
	 */
	function generateSalt()
	{
		$salt = $this->_saltPrefix;
		$length = $this->_saltLength;
		$itoa64 = $this->_itoa64;
		for ($i=0; $i < $length; $i++) $salt .= $itoa64{mt_rand(0, 63)};
		return $salt;
	}


	/**
	 * Generate password.
	 * @param  int    $length
	 * @return string
	 */
	function generatePassword($length=12)
	{
		$pass = '';
		$itoa64 = $this->_itoa64;
		for ($i=0; $i < $length; $i++) $pass .= $itoa64{mt_rand(0, 63)};
		return $pass;
	}


	/**
	 * Encrypt password.
	 * @param  string $pass password
	 * @param  string $salt salt, including all it's prefixes
	 * @see    self::generateSalt()
	 * @return string
	 */
	function encrypt($pass, $salt=null)
	{
		return crypt($pass, ($salt) ?: self::generateSalt());
	}


	/**
	 * Check password.
	 * @param  string $hash encrypted hash
	 * @param  string $pass password
	 * @return bool
	 */
	function check($hash, $pass)
	{
		return ($hash == crypt($pass, substr($hash, 0, strlen($this->_saltPrefix) + $this->_saltLength)));
	}
}
