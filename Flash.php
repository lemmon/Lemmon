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
 * Library for handling flash messages.
 */
class Flash
{
	protected $route;


	/**
	 * Constructor.
	 * @param  Route $route
	 */
	final function __construct($route)
	{
		$this->route = $route;
		
		// generate new hash on new page
		if (self::getHash() != ($hash=self::generateHash((string)$route->getSelf()->includeQuery(), $_SERVER['REQUEST_METHOD']=='POST')))
		{
			$_SESSION['__FLASH__'] = ['hash' => $hash];
		}
	}


	/**
	 * Assign new link for redirects.
	 * @param  string $link
	 * @param  bool   $post
	 * @return Flash
	 */
	function assignNewLink($link, $post=null)
	{
		$_SESSION['__FLASH__']['hash'] = self::generateHash($link, $post);
		return $this;
	}


	/**
	 * @param  string $link
	 * @param  bool   $post
	 * @return string
	 */
	function generateHash($link, $post=null)
	{
		return (($post ? microtime(1).'@' : '') . (string)$link);
	}


	/**
	 * @return string
	 */
	function getHash()
	{
		return $_SESSION['__FLASH__']['hash'];
	}


	/**
	 * Notice.
	 * @param  string $message
	 * @return Flash
	 */
	function notice($message)
	{
		$_SESSION['__FLASH__']['messages']['notice'][] = call_user_func_array(array($this, '_message'), func_get_args());
		return $this;
	}


	/**
	 * Error.
	 * @param  string $message
	 * @return Flash
	 */
	function error($message)
	{
		$_SESSION['__FLASH__']['messages']['error'][] = call_user_func_array(array($this, '_message'), func_get_args());
		return $this;
	}


	/**
	 * Error on field.
	 * @param  string $field
	 * @param  string $message
	 * @return Flash
	 */
	function errorField($field, $message='')
	{
		$_SESSION['__FLASH__']['error_fields'][$field][] = $message;
		return $this;
	}


	/**
	 * Process the message.
	 */
	private function _message($message)
	{
		return call_user_func_array('Lemmon_I18n::t', func_get_args());
	}
}