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

        // remove strings
        $strings = [];
        $i = 0;
        $expr = preg_replace_callback('/("([^"]+)"|\'([^\']+)\'|{([^}]+)})/', function($m) use (&$i, &$strings){
            $i++;
            $strings[$i] = $m[0];
            return ':@' . $i;
        }, $expr);
        
        // quote fields
        $expr = preg_replace_callback('/[0-9_]*[a-z][a-z0-9_\.]*\*?/', function($m){
            return Quote::field($m[0]);
        }, $expr);
        
        // paste strings back
        foreach ($strings as $i => $string) {
            $expr = str_replace(':@' . $i, $string, $expr);
        }

        // match and replace arguments
        if ($args) {
            $expr = preg_replace_callback('/(%?((\?)|:(\d+))%?)({(\w+)})?/', function($m) use ($args){
                static $i = 0;
                $arg = $args[$m[4] ? $m[4] - 1 : $i];
                if (is_object($arg)) {
                    if ($arg instanceof self) {
                        $res = $arg->toString();
                    } else {
                        throw new \Exception(sprintf('Unknown argument type (%s)', get_class($arg)));
                    }
                } else {
                    $val = str_replace($m[2], $arg, $m[1]);
                    switch ($m[6]) {
                        case 'raw':
                            $res = $val;
                            break;
                        case 'field':
                            $res = Quote::field($val);
                            break;
                        default:
                            $res = Quote::value($val);
                    }
                }
                if (!$m[4]) {
                    $i++;
                }
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
