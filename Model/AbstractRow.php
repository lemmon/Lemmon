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

use \Lemmon\Db\Adapter,
    \Zend\Db\RowGateway\RowGateway,
    \Zend\Db\TableGateway\TableGateway,
    \Zend\Db\TableGateway\Feature\RowGatewayFeature;

/**
 * Model.
 */
abstract class AbstractRow
{
	private $_zend;
	private $_model;
	private $_current;
	
	protected $table;
	protected $primary;
	protected $require;
	protected $timeStampable;
	
	
	final function __construct($id=null)
	{
		$db = Adapter::getInstance();
		
		$this->_model = new $this->model;

		// table name
		if (!$this->table)
		{
			$this->table = $this->_model->getTable();
		}
		
		// primary key
		if (!is_array($this->primary))
		{
			#$this->primary = [$this->primary];
		}

		// current
		if ($id)
		{
			$table = new TableGateway($this->table, $db, new RowGatewayFeature($this->primary));
			$results = $table->select($this->_getPrimaryData($id));
			$this->_zend = $results->current();
		}
		// new
		else
		{
			$this->_zend = new RowGateway(
				$this->primary,
				$this->table,
				$db);
		}
	}


	private function _getPrimaryData($id)
	{
		return array_combine(
			(array)$this->primary,
			is_array($id) ? $id : [$id]
			);
	}


	function getTable()
	{
		return $this->table;
	}


	function __set($key, $val)
	{
		$this->_zend[$key] = $val;
	}


	function save()
	{
		$this->_validate([]);
		$this->_zend->save();
	}


	private function _validate($f)
	{
		dump($f);
		die('--x');
	}
}
