<?php

namespace Lemmon\Db;

abstract class AbstractConnection
{
    private $_client;
    private $_connection;
    private $_namespace = '';

    protected $db;


    function getConnection()
    {
        return $this->_client ?: $this->_client = new \MongoClient;
    }


    function getDb()
    {
        return @$this->_connection ?: $this->_connection = $this->getConnection()->selectDB($this->db ?: $this->__db());
    }


    function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }


    function __get($name)
    {
        if ('@' == $name{0}) {
            $model = $this->_namespace . substr($name, 1);
            return new $model(new SimpleCollection($this, $this->getDb()->selectCollection($model::$table), $model));
        } else {
            return new SimpleCollection($this, $this->getDb()->selectCollection($name));
        }
    }


    private function _find($name)
    {
        if ('@' === $name{0}) {
            $q = explode('.', $name);
            $m = substr(array_shift($q), 1);
            $model = $this->_namespace . $m;
            $res = new SimpleCollection($this, $this->getDb()->selectCollection($model::$table), $model);
            foreach ($q as $_query) {
                $model::{'query' . $_query}($res);
            }
            return $res;
        } else {
            return new SimpleCollection($this, $this->getDb()->selectCollection($name));
        }
    }


    function find($name, $query = [])
    {
        $res = $this->_find($name);
        return is_array($query) ? $res->find($query) : $res->first($query);
    }


    function ai($key)
    {
        return $this->getDb()->counters->findAndModify(['_id' => $key], ['$inc' => ['n' => 1]], [], ['new' => TRUE, 'upsert' => TRUE])['n'];
    }
}
