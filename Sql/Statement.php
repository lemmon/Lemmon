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
 * SQL Statement.
 */
class Statement extends AbstractStatement
{
    private $_expression;


    function setQuery($query)
    {
        $this->_expression = new Expression(func_get_args());
        return $this;
    }


    function getQueryString()
    {
        if (isset($this->_expression)) {
            return (string)$this->_expression;
        } else {
            throw new \Exception('No query is defined.');
        }
    }
}