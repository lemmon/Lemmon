<?php

namespace Lemmon\Db;

use PDO;

/**
 * Statement.
 *
 * @copyright  Copyright (c) 2007-2012 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Statement extends \PDOStatement
{
	/** @var Connection */
	private $_connection;

	/**
	 * Constructor.
	 */
	protected function __construct(Connection $connection)
	{
		$this->_connection = $connection;
		$this->setFetchMode(PDO::FETCH_CLASS, 'Lemmon\Db\Row', array($this));
	}

	/**
	 * Returns connection.
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->_connection;
	}
}
