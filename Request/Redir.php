<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Request;

use Lemmon;

/**
 * Library for handling requests.
 */
class Redir
{
	private $_link;
	private $_code;

	private $_controller;


	/**
	 * Constructor.
	 * @param string    $link
	 * @param Framework $controller
	 */
	function __construct($link, \Lemmon\Framework $controller = null)
	{
		$this->_link = (string)$link;
		$this->_controller = $controller;
	}


	/**
	 * @return string
	 */
	function __toString()
	{
		return $this->_link;
	}


	/**
	 * Execute redirect.
	 * @param string $link
	 */
	public function exec()
	{
		// get link to redirect
		$link = (string)$this;

		// assign new hash for flash messages
		if ($c = $this->_controller)
		{
			$c->getFlash()->assignNewLink($link);
		}

		$_SESSION['x'] = 'y';
		// redirect
		if ($this->_code)
		{
			header('Location: ' . $link, true, $this->_code);
		}
		else
		{
			header('Location: ' . $link);
		}
		exit;
	}


	/**
	 * Sets redirect code.
	 * @param  int  $code
	 * @return Redir
	 */
	public function setCode($code)
	{
		$this->_code = $code;
		return $this;
	}
}
