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
 * Handles mails.
 */
abstract class Mailer implements \Lemmon\Mailer\IMailer
{


	final function __construct()
	{
		// init
		if (method_exists($this, '__init'))
		{
			$this->__init();
		}
	}


	private function _getMail()
	{
		return $this->__initMail();
	}


	final static function __callStatic($class_name, $arguments)
	{
		$called_class_name = get_called_class();
		preg_match('/(batchSend|preview|send)(.*)/i', $class_name, $m);
		if ($m[1] and $m[2])
		{
			$mailer = new $called_class_name();
			$method_name = lcfirst($m[2]);
			if (method_exists($mailer, $method_name))
			{
				array_unshift($arguments, $mail=$mailer->_getMail());
				$mail = call_user_func_array(array($mailer, $m[2]), $arguments);
				if (method_exists($mail, $m[1]))
				{
					return $mail->{$m[1]}();
					#return $mail;
				}
				elseif (method_exists($this, $m[1]))
				{
					$this->{$m[1]}();
					return $this;
				}
				else
				{
					throw new \Exception(sprintf('Unablo to perform method %s() on %s class.', $method_name));
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
