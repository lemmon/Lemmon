<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Form;

/**
 * Flash notices.
 */
class Flash
{
	private $_route;
	private $_link;
	private $_messages = [];
	private $_fields = [];


	function __construct(\Lemmon\Route $route)
	{
		// route
		$this->_route = $route;
		// links stored in sessions
		if ($flash = $_SESSION['__FLASH_MESSAGES__'])
		{
			$this->_messages = $flash['messages'];
			$this->_fields = $flash['fields'];
			unset($_SESSION['__FLASH_MESSAGES__']);
		}
	}


	function __destruct()
	{
		if (!$this->_link)
		{
			$_SESSION['__FLASH_MESSAGES__'] = [
				'messages' => $this->_messages,
				'fields'   => $this->_fields,
			];
		}
	}


	function setError($message)
	{
		$this->_messages['error'][] = $message;
		return $this;
	}


	function setErrorField($field, $message = '')
	{
		$this->_fields[$field][] = $message;
	}


	function setErrorFields(array $fields)
	{
		foreach ($fields as $key => $val)
		{
			if (is_int($key)) $this->setErrorField($val);
			else              $this->setErrorField($key, $val);
		}
	}


	function setNotice($message)
	{
		$this->_messages['notice'][] = $message;
		return $this;
	}


	function getNotices()
	{
		$this->_assignLink();
		return $this->_messages['notice'];
	}


	function getErrors()
	{
		$this->_assignLink();
		return $this->_messages['error'];
	}


	function assignNewLink()
	{
		// [depreciated] legacy fnc
	}


	private function _assignLink()
	{
		$this->_link = ($_POST ? microtime(true) : '') . '@' . (string)$this->_route->getSelf();
	}
}
