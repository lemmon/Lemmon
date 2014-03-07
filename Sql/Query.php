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

use \Lemmon\Db\Adapter as DbAdapter;

/**
 * SQL Query.
 */
class Query
{
    private $_adapter;


    function __construct($query = null, $adapter = null)
    {
        //
        // adapter
        if (is_null($adapter)) {
            // default adapter
            $this->_adapter = DbAdapter::getDefault();
        } elseif ($adapter instanceof DbAdapter) {
            // adapter passed
            $this->_adapter = $adapter;
        } else {
            // unknown adapter
            throw new \Exception('Unknown adapter.');
        }
        //
        // query
        if (isset($query)) {
            throw new \Exception('Not this way.');
        }
    }


    function select($table = null)
    {
        return new Select($this, is_array($table) ? $table : func_get_args());
    }


    function insert($table = null)
    {
        return new Insert($this, $table);
    }


    function update($table = null)
    {
        return new Update($this, $table);
    }


    function replace($table = null)
    {
        return new Replace($this, $table);
    }


    function delete($table = null)
    {
        return new Delete($this, $table);
    }


    function getAdapter()
    {
        return $this->_adapter;
    }


    function exec($query)
    {
        return $this->_adapter->__query($query);
    }
}