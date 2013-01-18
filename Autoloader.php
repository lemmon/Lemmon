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
 * Class autoloader.
 * 
 * @author Jakub Pelák <jpelak@gmail.com>
 */
class Autoloader
{
	const PREPEND = 1;
	const INCLUDE_PSR0 = 2;


	private $_masks = [];
	private $_files = [];


	/**
	 * Register masked class name.
	 * @param  string     $mask
	 * @param  string     $path
	 * @return Autoloader
	 */
	function addMask($mask, $path)
	{
		$this->_masks[] = ['mask' => '/^' . str_replace('*', '.+', $mask) . '$/', 'path' => $path];
	}


	/**
	 * Register file.
	 * @param  string     $file
	 * @return Autoloader
	 */
	function add($file)
	{
		$this->_files[] = $file;
	}


	/**
	 * Register.
	 * @param  int        $switch
	 * @return Autoloader
	 */
	function register($switch=null)
	{
		// prepend it
		if ($switch & self::PREPEND) $prepend = true; else $prepend = false;
		// register PSR-0
		if ($switch & self::INCLUDE_PSR0) $this->add('$lib/$class.php');
		// register
		spl_autoload_register(array($this, 'loadClass'), true, $prepend);
		//
		return $this;
	}


	function loadClass($class)
	{
		if ($file=$this->findFile($class)) require $file;
	}


	private function _parse($path, $class)
	{
		return str_replace(
			array('$root', '$lib', '$class', '$file'),
			array(
				ROOT_DIR,
				LIBS_DIR,
				str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class),
				strtolower(preg_replace('/(.)([A-Z])/u', '$1_$2', $class)),
				),
			$path);
	}


	function findFile($class)
	{
		// masks
		foreach ($this->_masks as $_mask) if (preg_match($_mask['mask'], $class))
		{
			if (is_callable($_mask['path'])) $_mask['path'] = $_mask['path']($class);
			$path = $this->_parse($_mask['path'], $class);
			if (is_file($path)) return $path;
		}
		// files
		foreach ($this->_files as $path)
		{
			$file = $this->_parse($path, $class);
			if (is_file($file)) return $file;
		}
	}
}
