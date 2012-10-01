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
 * Handles mails.
 */
abstract class Mailer implements Mailer\MailerInterface
{


	final function __construct()
	{
		// init
		if (method_exists($this, '__init'))
		{
			$this->__init();
		}
	}


	protected function getMessage()
	{
		return $this->__initMessage();
	}


	protected function getTransport()
	{
		return $this->__initTransport();
	}


	final static function __callStatic($class_name, $arguments)
	{
		$called_class_name = get_called_class();
		preg_match('/([a-z]+)(.*)/', $class_name, $m);
		if ($m[1] and $m[2])
		{
			$mailer = new $called_class_name();
			$method_name = lcfirst($m[2]);
			if (method_exists($mailer, $method_name))
			{
				array_unshift($arguments, $mail=$mailer->getMessage());
				$mail = call_user_func_array(array($mailer, $m[2]), $arguments);
				if (method_exists($mailer, $m[1]))
				{
					$mailer->{$m[1]}($mail);
					return $this;
				}
				else
				{
					throw new \Exception(sprintf('Unable to perform method %s() on %s class.', $m[1], $called_class_name));
				}
			}
			else
			{
				throw new \Exception(sprintf('Unknown action %s() on %s mailer.', $method_name, $called_class_name));
			}
		}
		else
		{
			return false;
		}
	}
}
