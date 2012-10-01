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
 * Class for handling errors.
 * 
 * @author Jakub Pelák <jpelak@gmail.com>
 */
class ErrorHandler
{


	static function error($errno, $errstr, $error_file, $error_line, $context)
	{
		if (!(error_reporting() & $errno)) return;
		throw new \ErrorException($errstr, 0, $errno, $error_file, $error_line);
	}
}
