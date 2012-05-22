<?php
/**
 * Handles general CRUD operations and extended scaffolding functions.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_Scaffold extends Application
{
	protected $_model;
	protected $_nullValues = true;
	protected $_loadLevel = 2;
	protected $_method = 'replace';
	protected $_paginate;
	protected $_redir = ':section';
	protected $_default = array();
	
	public function __init()
	{
		parent::__init();
		
		if (!$this->_model)
		{
			$model_name = $this->route->controller;
			$model_name = explode('/', $model_name);
			$model_name = end($model_name);
			$model_name = Lemmon\String::tableToClassName($model_name);
			$this->_model = $model_name;
		}
	}

	public function index()
	{
		$model = Lemmon_Model::factory($this->_model);
		if ($_paginate=$this->_paginate)
		{
			$page = (int)$this->route->page;
			if (is_array($_paginate))
			{
				$data = $model->paginate($page, $paginate, $_paginate[0], $_paginate[1]);
			}
			else
			{
				$data = $model->paginate($page, $paginate, $_paginate);
			}
		}
		else
		{
			$data = $model->all();
		}
		$this->data['data'] = $data;
		$this->data['paginate'] = $paginate;
	}

	public function create()
	{
		$model = Lemmon_Model::factory($this->_model);
		if ($f=$_POST)
		{
			if ($this->_sanitize($f)!==false and $model->create($f))
			{
				$this->flash->notice('Entry has been successfully created');
				return $this->request->redir($this->_redir, $model);
			}
			else
			{
				$this->flash->error('Entry has not been created');
			}
		}
		else
		{
			$this->data['f'] = $this->_default;
		}
		$this->data['item'] = $model;
	}
	
	public function update()
	{
		if ($id=$this->route->id)
		{
			$model = Lemmon_Model::factory($this->_model, $id);
			if ($model->load() and $model->getRowState()!='n/a')
			{
				if ($f=$_POST)
				{
					if ($this->_sanitize($f)!==false and $model->replace($f))
					{
						$this->flash->notice('Entry has been successfully updated');
						return $this->request->redir($this->_redir, $model);
					}
					else
					{
						$this->flash->error('Entry has not been updated');
					}
				}
				else
				{
					$this->data['f'] = $model->toArray($this->_loadLevel);
				}
				$this->data['item'] = $model;
			}
			else
			{
				throw new Lemmon_HTTP_Exception('404');
			}
		}
	}
	
	public function delete()
	{
		if ($id=$this->route->id)
		{
			$model = Lemmon_Model::factory($this->_model, $id);
			if ($model->load() and $model->getRowState()!='n/a')
			{
				if ($model->delete())
				{
					$this->flash->notice('Entry has been removed successfully');
				}
				else
				{
					$this->flash->error('Entry has not been deleted');
				}
				return $this->request->redir($this->_redir);
			}
			else
			{
				throw new Lemmon_HTTP_Exception('404');
			}
		}
	}

	private function _sanitize(&$f)
	{
		// null values
		if ($this->_nullValues)
		{
			foreach ($f as $field => $value)
			{
				if (!$value) $f[$field] = null;
			}
		}
	}
}
