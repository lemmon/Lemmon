<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Files;

/**
 * File.
 */
class File
{
	private $_name;
	private $_dir;


	function __construct($file)
	{
		$this->_name = basename($file);
		$this->_dir  = dirname($file);
	}
}
