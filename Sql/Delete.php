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
 * SQL Replace.
 */
class Delete extends AbstractStatement
{


    function getQueryString()
    {
        $q = [];
        // delete
        $q[] = 'DELETE';
        // from
        $q[] = 'FROM ' . $this->_table;
        // where
        if ($this->_where) $q[] = 'WHERE ' . join(' AND ', $this->_where);
        // order
        if ($this->_order) $q[] = 'ORDER BY ' . $this->_order;
        // limit
        if ($this->_limit) $q[] = 'LIMIT ' . $this->_limit;
        //
        return join(' ', $q);
    }


    function setTable($table)
    {
        if (!is_string($table)) throw new \Exception('Only single table is allowed on Delete query at this time.');
        parent::setTable($table);
    }
}