<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
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
	function __construct($link, \Lemmon\Framework $controller=null)
	{
		$this->_link = $link;
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
		if ($c=$this->_controller)
		{
			$c->getFlash()->assignNewLink($link);
		}

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
	
	
	public function setCode($code)
	{
		$this->_code = $code;
		return $this;
	}
	
	
	public function onDomain($domain)
	{
		$this->_domain = $domain;
		$this->onSubdomain('www');
		return $this;
	}
	
	
	private function _getDomain()
	{
		return $this->_domain ? $this->_domain : Lemmon_Route::getInstance()->getDomain();
	}
	
	
	public function onSubdomain($subdomain)
	{
		$this->_host = $subdomain . '.' . $this->_getDomain();
		if (!$this->_service)
		{
			$this->_service = 'http://';
		}
		return $this;
	}
}
