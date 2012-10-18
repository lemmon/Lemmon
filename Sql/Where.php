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
 * SQL Where.
 */
class Where
{
	private $_table;
	private $_originalExpression;
	private $_expressionString;


	function __construct(Table $table, $expr, $value=false)
	{
		$expression = '';
		$args = func_get_args();
		array_shift($args);
		
		//
		// one argument
		if ($value === false)
		{
			if ($expr instanceof Expression)
			{
				$expression = $expr;
			}
			else
			{
				$expression = new Expression($expr);
			}
		}
		//
		// three or more arguments
		elseif (func_num_args() > 3)
		{
			$expression = new Expression($args);
		}
		//
		// two arguments
		else
		{
			// expr ... ?
			if (is_string($expr) and strpos($expr, '?'))
			{
				$expression = new Expression($expr, $value);
			}
			// field IS NULL
			elseif (is_null($value))
			{
				if ($expr{0}=='!')
				{
					$expression = sprintf('%s IS NOT NULL', Quote::field($table->getAlias() . '.' . substr($expr, 1)));
				}
				else
				{
					$expression = sprintf('%s IS NULL', Quote::field($table->getAlias() . '.' . $expr));
				}
			}
			// field (NOT) IN (array)
			elseif (is_array($value))
			{
				if ($expr{0}=='!')
				{
					$expression = sprintf('%s NOT IN (%s)', Quote::field($table->getAlias() . '.' . substr($expr, 1)), Quote::value($value));
				}
				else
				{
					$expression = sprintf('%s IN (%s)', Quote::field($table->getAlias() . '.' . $expr), Quote::value($value));
				}
			}
			// field = value
			elseif (!is_numeric($expr))
			{
				// expression
				if (is_object($value) and $value instanceof Expression)
				{
					$expression = new Expression(sprintf('%s.%s = ?', $table->getAlias(), $expr), $value);
				}
				else
				{
					if ($expr{0}=='!')
					{
						$expression = sprintf('%s != %s', Quote::field($table->getAlias() . '.' . substr($expr, 1)), Quote::value($value));
					}
					else
					{
						$expression = sprintf('%s = %s', Quote::field($table->getAlias() . '.' . $expr), Quote::value($value));
					}
				}
			}
			// error
			else
			{
				throw new \Exception(sprintf('Unknown field name %s.', $expr));
			}
		}

		//
		if ($expression instanceof Expression)
		{
			$this->_originalExpression = $expression;
			$this->_expressionString = $expression->toString();
		}
		else
		{
			$this->_originalExpression = $args;
			$this->_expressionString = $expression;
		}
		
		// table
		$this->_table = $table;
	}


	function __toString()
	{
		return $this->_expressionString;
	}


	function getExpression()
	{
		return $this->_expressionString;
	}
}