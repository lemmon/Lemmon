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
 * Handles authentication. Uses Eksblowfish method with per-round salted passwords.
 * Even two identical passwords stored in the same database return different hashes
 * which increases security rapidly.
 */
abstract class AbstractAuth extends Service
{
	/** @var self */
	private static $_instance;
	
	/** @var mixed */
	private $_identity;


	/**
	 * Constructor.
	 */
	final function __construct() {
		// check for blowfish
		if (CRYPT_BLOWFISH != 1) {
			throw new Exception('Bcrypt not supported in this installation. See http://php.net/crypt');
		}
		
		// init
		$this->__init();
		
		// instance
		self::$_instance = $this;
	}


	/**
	 * Returns current instance.
	 * @return self
	 */
	static function getInstance()
	{
		if ($instance = self::$_instance) {
			return $instance;
		} else {
			return new self();
		}
	}


	protected function __init() {}


	/**
	 * Authenticate event.
	 * @param  string $username
	 * @param  string $password
	 * @see    self::authenticate()
	 * @return mixed
	 */
	protected function onAuthenticate($username, $password) {}
	protected function onStoreIdentity($identity, $permanent = false) {}
	protected function onClearIdentity(){}


	protected function onGetIdentity($identity)
	{
		return $identity;
	}


	/**
	 * Authenticate.
	 * @param  string $username
	 * @param  string $password
	 * @see    self::onAuthenticate()
	 * @return bool
	 */
	final function authenticate($username, $password)
	{
		if ($identity = $this->onAuthenticate($username, $password)) {
			// successful attempt
			$this->_identity = $identity;
			return true;
		} else {
			// authetication unsuccessful
			return false;
		}
	}


	/**
	 * Store identity.
	 * @param  bool $permanent
	 * @see    self::onStoreIdentity()
	 * @return mixed
	 */
	final function storeIdentity($permanent = false)
	{
		return $this->onStoreIdentity($this->_identity, $permanent);
	}


	/**
	 * Is there authenticated identity.
	 * @return bool
	 */
	final function hasIdentity()
	{
		return $this->_identity ? true : false;
	}


	/**
	 * Returns current identity.
	 * @see    self::getIdentity()
	 * @return mixed
	 */
	final function getIdentity()
	{
		if ($identity = $this->_identity) {
			return $this->onGetIdentity($identity);
		}
	}


	/**
	 * Sets identity.
	 * @param  mixed $identity
	 */
	final function setIdentity($identity)
	{
		$this->_identity = $identity;
	}


	/**
	 * Clear current identity.
	 * @see    self::onClearIdentity()
	 */
	final function clearIdentity()
	{
		$this->_identity = null;
		return $this->onClearIdentity();
	}
}
