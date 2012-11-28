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
 * Library for handling requests.
 */
class Request
{
	private $_controller;


	/**
	 * Constructor.
	 * @param Framework $controller
	 */
	function __construct($controller)
	{
		$this->_controller = $controller;
	}


	/**
	 * Access Redir class.
	 * @param  string        $link
	 * @param  mixed         $params
	 * @return Request\Redir
	 */
	function redir($link, $params = null)
	{
		if ($redir = $_GET['redir'])
		{
			return new Request\Redir($redir, $this->_controller);
		}
		else
		{
			return new Request\Redir($this->_controller->getRoute()->to($link, $params), $this->_controller);
		}
	}
}