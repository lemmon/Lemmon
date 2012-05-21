<?php
/**
* 
*/
class Lemmon_Route_Redir
{
	private $_service;
	private $_host;
	private $_domain;
	private $_route;
	private $_code;
	

	public function __construct($route, $host='', $service='')
	{
		$this->_service = $service;
		$this->_host = $host;
		$this->_route = $route;
	}
	

	public function __toString()
	{
		return $this->_service . $this->_host . $this->_route;
	}
	
	
	public function exec()
	{
		$to = (string)$this;
		$_SESSION['_flash']['link'] = md5($to);
		if ($this->_code)
		{
			header('Location: ' . $to, true, $this->_code);
		}
		else
		{
			header('Location: ' . $to);
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
