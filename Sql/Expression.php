<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
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
	private $_query;
	private $_expression;


	function __construct($query, array $expression)
	{
		$this->_query = $query;
		//
		$args = $expression;
		$expr = array_shift($args);
		//
		
		$expr = preg_replace_callback('/([a-z0-9]+)\(/', function($m){
			return strtoupper($m[1]) . '(';
		}, $expr);
		
		$expr = preg_replace_callback('/[a-z\_][a-z0-9\_\.]+/', function($m) use ($query){
			return $query->quoteField($m[0]);
		}, $expr);
		
		if ($args)
		{
			$expr = preg_replace_callback('/%?(\?)%?/', function($m) use ($args, $query){
				static $i = 0;
				$res = $query->quote(($m[0]=='?') ? $args[$i] : str_replace('?', $args[$i], $m[0]));
				$i++;
				return $res;
			}, $expr);
		}
		
		//
		$this->_expression = $expr;
	}


	function __toString()
	{
		return $this->_expression;
	}
}
