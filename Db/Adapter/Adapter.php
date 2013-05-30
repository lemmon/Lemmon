<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Db\Adapter;

/**
 * Db Adapter.
 */
class Adapter
{
    static private $_default;
    
    private $_pdo;


    function __construct($driver)
    {
        $pdo = new \PDO("mysql:dbname={$driver['database']};host={$driver['host']}", $driver['username'], $driver['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$driver['encoding']}'"]);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); 
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ); 
        
        $this->_pdo = $pdo;
        
        // default adapter
        if (!isset(self::$_default)) {
            self::$_default = $this;
        }
    }


    static function getDefault()
    {
        if ($adapter = self::$_default) {
            return $adapter;
        } else {
            throw new \Exception('No adapter has been defined yet.');
        }
    }


    function getPdo()
    {
        return $this->_pdo;
    }


    function query()
    {
        return new \Lemmon\Sql\Query();
    }
}
