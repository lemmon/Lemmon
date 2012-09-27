<?php

/*
 * This file is part of the Lemmon package.
 *
 * (c) Jakub Pelák <jpelak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Model;

use \Zend\Db\TableGateway\AbstractTableGateway;

/**
 * Model.
 */
abstract class AbstractModel
{
	private $_zend;
	private $_row;
	
	protected $table;
	protected $row;
	protected $primary;


	final function __construct()
	{
	}


	function getTable()
	{
		return $this->table;
	}
}
