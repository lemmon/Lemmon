<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Db;

/**
 * Db Adapter.
 */
class Adapter
{
    static private $_default;
    static private $_adapters = [];

    private $_name;
    private $_pdo;


    protected function __logQuery($query, $t){}


    function __construct(array $config = [])
    {
        $this->_pdo = new \PDO("mysql:dbname={$config['database']};host={$config['host']}", $config['username'], $config['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$config['encoding']}'"]);
        $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); 
        $this->_pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ); 
        
        // adapters
        $this->_name = $name = $config['name'] ?: get_class($this);
        if (array_key_exists($name, self::$_adapters)) {
            throw new \Exception(sprintf('Adapter "%s" already exists.', $name));
        }
        self::$_adapters[$name] = $this;
        
        // default adapter
        if (!isset(self::$_default)) {
            self::$_default = $name;
        }
    }


    static function getDefault()
    {
        if ($adapter_name = self::$_default) {
            return self::$_adapters[$adapter_name];
        } else {
            throw new \Exception('No adapter has been defined yet');
        }
    }


    static function get($adapter_name)
    {
        if ($adapter = self::$_adapters[$adapter_name]) {
            return $adapter;
        } else {
            throw new \Exception(sprintf('Unknown Adapter (%s)', $adapter_name));
        }
    }


    function __query($query)
    {
        $t = microtime(true);
        $res = $this->_pdo->query($query);
        $this->__logQuery($query, microtime(true) - $t);
        return $res;
    }


    function getPdo()
    {
        return $this->_pdo;
    }


    function __call($name, $arguments)
    {
        if (substr($name, 0, 4) == 'find') {
            $_name = substr($name, 4);
            return new $_name($this, $arguments[0]);
        } elseif (substr($name, 0, 3) == 'get') {
            // TODO: need to improve this to work withou calling static method
            return call_user_func([substr($name, 3), 'find'], $arguments[0], $this);
        } else {
            throw new \Exception(sprintf('Unkown method (%s)', $name));
        }
    }


    /**
     * Find Model.
     */
    function find($model_name, array $cond = [])
    {
        // depreciated
        return new $model_name($this, $cond);
    }


    /**
     * Load Row.
     */
    function load($row_name, $cond = null)
    {
        // depreciated
        return call_user_func([$row_name, 'find'], $cond, $this);
    }


    function query($query = null)
    {
        return new \Lemmon\Sql\Query($query, $this);
    }


    function select($table = null)
    {
        return $this->query()->select($table);
    }
}
