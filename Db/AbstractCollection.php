<?php

namespace Lemmon\Db;

abstract class AbstractCollection implements \IteratorAggregate
{
    private $_connection;
    private $_collection;
    private $_model;
    private $_query = [];


    final function __construct(AbstractConnection $connection, \MongoCollection $collection, $model = NULL)
    {
        $this->_connection = $connection;
        $this->_collection = $collection;
        $this->_model = $model;
    }


    final function getConnection()
    {
        return $this->_connection;
    }


    final function getCollection()
    {
        return $this->_collection;
    }


    function getIterator()
    {
        return $this->all();
    }


    private function _query($query = [])
    {
        // merge query
        if (is_string($query)) {
            $query = ['_id' => new \MongoId($query)];
        } elseif (is_object($query) and $query instanceof \MongoId) {
            $query = ['_id' => $query];
        } elseif (is_array($query)) {
            $query = array_merge_recursive($this->_query, $query);
        } else {
            throw new \Exception('Invalid query type');
        }
        // restrictions
        if ($_model = $this->_model and defined($_model . '::RESTRICT')) {
            $query = array_replace($query, $_model::RESTRICT);
        }
        //
        return $query;
    }


    function query($query, ...$args)
    {
        if ($model = $this->_model) {
            $model::{'query' . $query}($this, ...$args);
            return $this;
        } else {
            throw new \Exception('No model defined');
        }
    }


    function find($query = [])
    {
        $this->_query = $this->_query($query);
        return $this;
    }


    function distinct($field, $query = [])
    {
        return $this->_collection->distinct($field, $this->_query($query));
    }


    function first($query = [])
    {
        if ($object = $this->_collection->findOne($this->_query($query))) {
            if ($model = $this->_model) {
                return new $model($this, $object);
            } else {
                return $object;
            }
        }
    }


    function all(array $query = [], array $fields = [])
    {
        $cursor = $this->_collection->find($this->_query($query), $fields);
        if ($model = $this->_model) {
            return new SimpleModelContainer($this, $cursor, $model);
        } else {
            return $cursor;
        }
    }


    function count(array $query = [])
    {
        return $this->_collection->count($this->_query($query));
    }
}
