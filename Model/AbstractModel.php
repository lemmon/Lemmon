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

use \Lemmon\Db\Adapter as DbAdapter,
    \Lemmon\Sql\Statement as SqlStatement;

/**
 * Model.
 */
abstract class AbstractModel implements \IteratorAggregate, \ArrayAccess
{
    const FETCH_AS_ARRAY = 1;

    static $adapter;
    static $rowClass;
    static $table;
    static $primary = 'id';
    static $fields;
    static $uploads;
    static $sanitize;
    static $required;
    static $unique;
    static $timestamp;
    static $hasOne;
    static $hasMany;
    static $belongsTo;
    static $hasAndBelongsToMany;
    static $uploadDir;

    private $_adapter;
    private $_query;
    private $_statement;
    private $_schema;

    private $_all;


    protected function __init() {}


    final function __construct($adapter = null, array $cond = [])
    {
        // adapter
        if ($adapter or $adapter = self::$adapter) {
            if ($adapter instanceof DbAdapter) {
                $this->_adapter = $adapter;
            } elseif (is_string($adapter)) {
                $this->_adapter = DbAdapter::get($adapter);
            } else {
                throw new \Exception(sprintf('Unknown adapter type: %s.', gettype($adapter)));
            }
        } else {
            // default adapter
            $this->_adapter = DbAdapter::getDefault();
        }
        
        // query
        $this->_query = $this->_adapter->query();
        $this->_statement = new SqlStatement($this->_query);
        
        // schema
        $this->_schema = Schema::factory(get_class($this));
        
        // table
        $this->_statement->setTable($this->_schema->get('table'));
        
        // init model
        $this->__init();
        
        // where
        if ($cond) {
            $this->_statement->where($cond);
        }
    }


    function __call($method, $args)
    {
        if (method_exists($this->_statement, $method)) {
            unset($this->_all);
            call_user_func_array([$this->_statement, $method], $args);
            return $this;
        } else {
            throw new \Exception(sprintf('Unknown method %s().', $method));
        }
    }


    static function find($cond = null, DbAdapter $adapter = null)
    {
        // model
        $class_name = get_called_class();
        $model = new $class_name($adapter);
        // where
        if (is_int($cond) or is_string($cond)) {
            // returns Row
            return $model->wherePrimary($cond);
        } elseif (is_array($cond)) {
            // returns Row
            return $model->where($cond);
        } elseif (is_null($cond)) {
            // returns Model
            return $model;
        } else {
            // error
            throw new \Exception(sprintf('Unknown condition type: %s.', gettype($cond)));
        }
    }


    final function getAdapter()
    {
        return $this->_adapter;
    }


    final function getSchema()
    {
        return $this->_schema;
    }


    final function wherePrimary($id)
    {
        return $this->where([$this->_schema->primary[0] => $id]);
    }


    final function create()
    {
        return new $this->_schema->rowClass([], $this->_adapter, $this);
    }


    final function getIterator()
    {
        return new \ArrayIterator($this->all());
    }


    final function offsetExists($i)
    {
        $all = ($this->_all) ?: ($this->_all = $this->all());
        return array_key_exists($i, $all);
    }


    final function offsetGet($i)
    {
        $all = ($this->_all) ?: ($this->_all = $this->all());
        return $all[$i];
    }


    final function offsetSet($offset, $value) { return false; }
    final function offsetUnset($offset) { return false; }


    final function getStatement()
    {
        return $this->_statement;
    }


    private function _getIterator()
    {
        $query = new \Lemmon\Sql\Select($this->getStatement());
        $query->cols($this->_schema->table . '.*');
        $pdo_statement = $query->exec();
        $pdo_statement->setFetchMode(\PDO::FETCH_ASSOC);
        return $pdo_statement;
    }


    final function count()
    {
        if ($this->_all) {
            return count($this->_all);
        } else {
            $query = new \Lemmon\Sql\Select($this->_statement);
            return $query->count();
        }
    }


    final function all()
    {
        $res = [];
        $rowClass = $this->_schema->rowClass;
        foreach ($this->_getIterator()->fetchAll() as $row) {
            $res[] = new $rowClass($row, $this->_adapter, $this, true);
        }
        return $res;
    }


    final function allByPrimary()
    {
        $res = [];
        $rowClass = $this->_schema->rowClass;
        foreach ($this->_getIterator()->fetchAll() as $row) {
            $res[$row['id']] = new $rowClass($row, $this->_adapter, $this, true);
        }
        return $res;
    }


    final function first()
    {
        $rowClass = $this->_schema->rowClass;
        if ($row = $this->_getIterator()->fetch()) {
            return new $rowClass($row, $this->_adapter, $this, true);
        }
    }
}
