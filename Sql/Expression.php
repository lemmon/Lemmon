<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Sql;

/**
 * SQL Expression.
 */
class Expression
{
	private $_originalExpression;
	private $_originalArguments;
	private $_expressionString;


	function __construct($expression)
	{
		$args = is_array($expression) ? $expression : func_get_args();
		$expr = array_shift($args);

		//
		$this->_originalExpression = $expr;
		$this->_originalArguments  = $args;

		// uppercase function names
		$expr = preg_replace_callback('/([a-z0-9]+)\(/', function($m){
			return strtoupper($m[1]) . '(';
		}, $expr);

		// quote fields
		$expr = preg_replace_callback('/[a-z\_][a-z0-9\_\.]+/', function($m){
			return Quote::field($m[0]);
		}, $expr);

		// match and replace arguments
		if ($args)
		{
			$expr = preg_replace_callback('/%?(\?)%?/', function($m) use ($args){
				static $i = 0;
				if (is_object($args[$i]))
				{
					if ($args[$i] instanceof self)
					{
						$res = (string)$args[$i];
					}
					else
					{
						throw new \Exception(sprintf('Unknown argument type (%s)', get_class($args[$i])));
					}
				}
				else
				{
					$res = Quote::value(($m[0]=='?') ? $args[$i] : str_replace('?', $args[$i], $m[0]));
				}
				$i++;
				return $res;
			}, $expr);
		}

		//
		return $this->_expressionString = $expr;
	}


	function __toString()
	{
		return $this->_expressionString;
	}


	function toString()
	{
		return $this->_expressionString;
	}
}
