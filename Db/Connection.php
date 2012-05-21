<?php

namespace Lemmon\Db;

use PDO;

/**
 * Handles database connections.
 *
 * @copyright  Copyright (c) 2007-2012 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Connection extends PDO
{
	/** @var string */
	private $_dsn;

	/** @var string */
	private $_tablePrefix = '';

	/**
	 * Constructor.
	 */
	function __construct($dsn, $username=NULL, $password=NULL)
	{
		parent::__construct($this->_dsn=$dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Lemmon\Db\Statement', array($this)));
	}
	
	/**
	 * Returns DSN.
	 */
	function getDsn()
	{
		return $this->_dsn;
	}

	/**
	 * Sets table prefix.
	 */
	function setTablePrefix($prefix)
	{
		$this->_tablePrefix = $prefix;
		return $this;
	}

	/**
	 * Returns table prefix.
	 */
	function getTablePrefix($prefix)
	{
		return $this->_tablePrefix;
	}

	/**
	 * Prepares MySQL query.
	 */
	function query($query)
	{
		$args = func_get_args();
		return $this->exec(new Query($connection, array_shift($args), $args));
	}
}
