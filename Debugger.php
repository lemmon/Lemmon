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
 * Handles debugging.
 */
class Debugger extends Debugger\AbstractDebugger
{


	static function init()
	{
		register_shutdown_function([__CLASS__, 'shutdownHandler']);
		set_exception_handler([__CLASS__, 'exceptionHandler']);
		set_error_handler([__CLASS__, 'errorHandler']);
	}


	static function dump($data)
	{
		self::includeHeaders();
		include __DIR__ . '/Debugger/_dump.php';
	}


	static function dumpArray($array)
	{
		if (is_array($array))
		{
			self::includeHeaders();
			include __DIR__ . '/Debugger/_dump_array.php';
		}
		else
		{
			throw new \Exception('This is not an array.');
		}
	}


	static function shutdownHandler()
	{
		$types = [
			E_ERROR         => 1,
			E_CORE_ERROR    => 1,
			E_COMPILE_ERROR => 1,
			E_PARSE         => 1,
		];
		$error = error_get_last();
		if (isset($types[$error['type']]))
		{
			include __DIR__ . '/Debugger/_fatal_error.php';
		}
	}


	static function exceptionHandler($exception)
	{
		include __DIR__ . '/Debugger/_exception.php';
	}


	static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno))
		{
			// This error code is not included in error_reporting
			return;
		}

		throw new \Exception($errstr);

		switch ($errno)
		{
			case E_USER_ERROR:
				echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
				echo "  Fatal error on line $errline in file $errfile";
				echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				echo "Aborting...<br />\n";
				exit(1);
				break;
			case E_USER_WARNING:
				echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
				break;
			case E_USER_NOTICE:
				echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
				break;
			default:
				echo "Unknown error type: [$errno] $errstr<br />\n";
				break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}
}
