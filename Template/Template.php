<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Template;

/**
 * Template.
 */
class Template
{
	static $_defaultFilesystem = [];
	static $_defaultEnvironment = [];

	private $_base;
	private $_name;
	private $_filesystem = [];
	private $_environment = [];
	private $_extension;


	final function __construct($base, $name)
	{
		$this->_filesystem = self::$_defaultFilesystem;
		$this->_environment = self::$_defaultEnvironment;
		
		$this->_base = $base;
		$this->setFilesystem([$base]);
		
		$this->display($name);
	}


	static function setDefaultEnvironment(array $env)
	{
		self::$_defaultEnvironment = $env;
	}


	function setExtension($extension)
	{
		$this->_extension = $extension;
		return $this;
	}


	function display($name)
	{
		/*
		if ($i = strrpos($name, '/'))
		{
			$dir = substr($name, 0, $i);
			$name = substr($name, strlen($dir) + 1);
			$this->appendFilesystem($dir);
		}
		*/
		$this->_name = $name;
		return $this;
	}


	function render($data)
	{
		//
		// filesystem
		$twig_loader = new \Twig_Loader_Filesystem($this->_filesystem);
		//
		// environment
		$twig_environment = new \Twig_Environment($twig_loader, $this->_environment);
		if ($this->_extension === null)
			$twig_environment->addExtension(new ExtensionTwig());
		elseif (is_object($this->_extension))
			$twig_environment->addExtension($this->_extension);
		//
		// template
		$twig = $twig_environment->loadTemplate($this->_name . '.html');
		//
		// display
		return $twig->render($data);
	}


	function setFilesystem(array $fs)
	{
		$this->_filesystem = $fs;
		return $this;
	}


	function appendFilesystem($filesystem)
	{
		foreach (explode('/', trim($filesystem, '/')) as $part)
		{
			array_unshift($this->_filesystem, $this->_filesystem[0] . '/' . $part);
		}
		return $this;
	}


	function setEnvironment(array $env)
	{
		$this->_environment = $env;
		return $this;
	}
}
