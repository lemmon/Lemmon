<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
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
	static private $_instance;
	
	private $_pdo;


	function __construct($driver=[])
	{
		$this->_pdo = new \PDO("mysql:dbname={$driver['database']};host={$driver['host']}", $driver['username'], $driver['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$driver['encoding']}'"]);
		$this->_pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ); 
	}


	static function getInstance()
	{
		return self::$_instance;
	}


	function getPdo()
	{
		return $this->_pdo;
	}


	function query()
	{
		return new \Lemmon\Sql\Query($this);
	}
}
