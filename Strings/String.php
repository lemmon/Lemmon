<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Strings;

/**
 * @author Jakub Pelák <jpelak@gmail.com>
 */
class String
{
    private $_encoding = 'UTF-8';
    private $_str;


    function __construct($str = '', $encoding = null)
    {
        if (!is_string($str)) {
            throw new \Exception('This is not a string');
        }
        if ($encoding) {
            $this->_encoding = $encoding;
        }
        $this->_str = $str;
    }


    function __toString()
    {
        return $this->getStr();
    }


    function __call($name, $args)
    {
        array_unshift($args, $this->_str);
        $res = call_user_func_array(__NAMESPACE__ . "\\Inflector::$name", $args);
        return is_string($res) ? new self($res, $this->_encoding) : $res;
    }


    function getStr()
    {
        return $this->_str;
    }


    function prepend($other_str)
    {
        return new self($other_str . $this->_str, $this->_encoding);
    }
}