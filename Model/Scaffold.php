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

/**
 * Model Scaffolding.
 */
abstract class Scaffold extends \Application
{
	protected $model;

	private $_model;


	public function __init()
	{
		parent::__init();
		
		// model name
		if (!$this->modelName)
		{
			$model = self::getController();
			$model = end(explode('/', $model));
			$model = \Lemmon\String::tableToClassName($model);
			$this->model = $model;
		}
		// model itself
		$this->_model = new $this->model;
	}


	function index()
	{
		if ($this->_model->count())
		{
			$this->data['data'] = $this->_model->all();
		}
		else
		{
			return \Lemmon\Template::display('empty', self::getData(true));
		}
	}


	private function _sanitize($f)
	{
		// sanitize fields
		foreach ($f as $key => $val)
		{
			if ($val) $f[$key] = trim($f[$key]);
			else $f[$key] = null;
		}
		//
		return $f;
	}


	function create()
	{
		$item = $this->_model->create();
		// on POST
		if ($f = $_POST)
		{
			// sanitize fields
			$f = $this->_sanitize($f);
			// save
			try
			{
				$item->set($f);
				$item->save();
				$this->flash->notice(\Lemmon_I18n::t('Item has been created'));
				return $this->request->redir(':section');
			}
			catch (\Lemmon\Model\ValidationException $e)
			{
				$this->flash->error($e->getMessage())
				            ->error(\Lemmon_I18n::t('Item has NOT been created'))
				            ->errorFields($e->getFields());
			}
		}
		// model
		$this->data['item'] = $item;
	}


	function update()
	{
		if ($id = $this->route->id and $item = $this->_model->wherePrimary($this->route->id)->first())
		{
			// POST
			if ($f = $_POST)
			{
				// sanitize fields
				$f = $this->_sanitize($f);
				// save
				try
				{
					$item->set($f);
					$item->save();
					$this->flash->notice(\Lemmon_I18n::t('Item has been updated'));
					return $this->request->redir(':section');
				}
				catch (\Lemmon\Model\ValidationException $e)
				{
					$this->flash->error($e->getMessage())
					            ->error(\Lemmon_I18n::t('Item has NOT been updated'))
					            ->errorFields($e->getFields());
				}
			}
			// default values
			else
			{
				$this->data['f'] = $item;
			}
			// model
			$this->data['item'] = $item;
		}
		else
		{
			throw new \Lemmon\Http\Exception(404, \Lemmon_I18n::t('Entry NOT found.'));
		}
	}


	function delete()
	{
		throw new \Exception('[todo]');
	}
}
