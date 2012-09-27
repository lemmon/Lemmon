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
 * SQL Where.
 */
class Where
{
	private $_statement;
	private $_expression;


	function __construct(QueryStatement $statement, $expr, $value=false)
	{
		$expression = '';
		
		// one expression
		if (is_a($expr, __NAMESPACE__ . '\Expression'))
		{
			$expression = $expr;
		}
		// one argument
		elseif ($value === false)
		{
			$expression = $statement->expr($expr);
		}
		// three or more arguments
		elseif (func_num_args()>3)
		{
			$args = func_get_args();
			array_shift($args);
			$expression = call_user_func_array([$statement, 'expr'], $args);
		}
		// two arguments
		else
		{
			// expression
			if (is_a($value, __NAMESPACE__ . '\Expression'))
			{
				$expression = $value;
			}
			// expr ... ?
			elseif (strpos($expr, '?'))
			{
				if (is_null($value) and strpos($expr, '!='))
				{
					// field != ? to field IS NOT NULL
					$expression = $statement->expr($expr);
					$expression = call_user_func([$statement, 'expr'], preg_replace('/\s+!=\s+/', ' IS NOT ', $expr), null);
				}
				elseif (is_null($value) and strpos($expr, '='))
				{
					// field = ? to field IS NULL
					$expression = $statement->expr($expr);
					$expression = call_user_func([$statement, 'expr'], preg_replace('/\s+=\s+/', ' IS ', $expr), null);
				}
				else
				{
					$expression = $statement->expr($expr);
					$expression = call_user_func([$statement, 'expr'], $expr, $value);
				}
			}
			// field IS NULL
			elseif (is_null($value))
			{
				$expression = sprintf('%s IS NULL', $statement->quoteField($expr));
			}
			// field (NOT) IN (array)
			elseif (is_array($value))
			{
				if (strtoupper(substr($expr, 0, 4))=='NOT ')
				{
					$expression = sprintf('%s NOT IN (%s)', $statement->quoteField(trim(substr($expr, 4))), join(', ', $statement->quoteArray($value)));
				}
				else
				{
					$expression = sprintf('%s IN (%s)', $statement->quoteField($expr), join(', ', $statement->quoteArray($value)));
				}
			}
			// field = value
			elseif (!is_numeric($expr))
			{
				$expression = sprintf('%s = %s', $statement->quoteField($expr), $statement->quote($value));
			}
			// error
			else
			{
				throw new \Exception(sprintf('Unknown field name %s.', $expr));
			}
		}
		
		$this->_statement = $statement;
		$this->_expression = $expression;
	}


	function __toString()
	{
		return (string)$this->getExpression();
	}


	function getExpression()
	{
		return $this->_expression;
	}
}