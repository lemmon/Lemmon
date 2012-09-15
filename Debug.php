<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon;

/**
 * Handles templates.
 */
class Debug
{
	static private $headersIncluded = false;


	function dump($data)
	{
		self::includeHeaders();
		include __DIR__ . '/Debug/_dump.php';
	}


	function includeHeaders($force=false)
	{
		if ($force or !self::$headersIncluded)
		{
			include __DIR__ . '/Debug/_headers.php';
			self::$headersIncluded = true;
		}
	}
}
