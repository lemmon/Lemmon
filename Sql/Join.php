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
 * SQL Join.
 */
class Join
{
    private $_table;
    private $_type = 'INNER';
    private $_join;
    private $_conditions = [];


    function __construct(Table $table, $join, $args)
    {
        // tables
        $this->_table = $table;
        $this->_join = $join = new Table($join);
        $join->forceName(true);
        // conditions
        foreach ($args as $key => $val) {
            if (is_string($val) and $val{0} == ':') {
                $args[$key] = new Expression(sprintf('%s.%s = %s.%s', $table->getAliasOrName(), substr($val, 1), $join->getAlias(), $key));
            } else {
                $args[$key] = new Where($join, $key, $val);
            }
        }
        $this->_conditions = $args;
    }


    function __toString()
    {
        return $this->toString();
    }


    function toString()
    {
        $q = [];
        // type
        $q[] = $this->_type;
        // join
        $q[] = 'JOIN';
        // table
        $q[] = $this->_join->toString();
        // conditions
        $q[] = 'ON';
        $q[] = '(' . join(' AND ', $this->_conditions) . ')';
        // 
        return join(' ', $q);
    }
}