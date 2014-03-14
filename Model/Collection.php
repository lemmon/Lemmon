<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Model;

use \Lemmon\Sql\Select;


/**
* Collection.
*/
class Collection
{
    private $_adapter;
    private $_collection;
    private $_many = [];


    function __construct(\Lemmon\Db\Adapter $adapter, array $collection = null)
    {
        $this->_adapter = $adapter;
        $this->_collection = $collection;
    }


    function __ids()
    {
        return array_map(function($item){
            return $item->__id();
        }, $this->_collection);
    }


    function fetchMany($model, $collection, $key, $id, $callback)
    {
        if (!array_key_exists($collection, $this->_many)) {
            foreach ($callback($this->_adapter)->where($this->_collection ? [$key => $this->__ids()] : [$key => $id]) as $item) {
                $this->_many[$collection][$item->{$key}][] = $item;
            }
        }
        return $this->_many[$collection][$id];
    }


    function getArray()
    {
        return $this->_collection;
    }


    function count()
    {
        return count($this->_collection);
    }


    function first()
    {
        return $this->_collection[0];
    }
}
