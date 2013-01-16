<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Form;

/**
 * Scaffolding.
 */
class Scaffold
{


	static function create(\Lemmon\Framework $controller, array $config = [])
	{
		//
		// model
		$model = self::_getModel($controller, $config);
		$item = $model->create();
		//
		// scaffolding
		if ($item = $model->create())
		{
			// POST
			if ($f = $_POST)
			{
				// sanitize fields
				$f = self::_sanitize($f);
				// save
				try
				{
					$item->set($f);
					$item->save();
					$controller->getFlash()->setNotice(\Lemmon_I18n::t('Item has been created'));
					return $controller->getRequest()->redir(($config['redir']) ?: ':section', $item);
				}
				catch (\Lemmon\Model\ValidationException $e)
				{
					$controller->getFlash()->setError($e->getMessage())
					                       ->setError(\Lemmon_I18n::t('Item has NOT been created'))
					                       ->setErrorFields($e->getFields());
				}
			}
			elseif ($config['default'] and is_array($config['default']))
			{
				// default values
				$controller->setData(['f' => $config['default']]);
			}
			// template data
			$controller->setData(['item' => $item]);
		}
	}


	static private function _getModel(\Lemmon\Framework $controller, array &$config = [])
	{
		//
		// model name
		if (!$config['model'])
		{
			$config['model'] = \Lemmon\String::tableToClassName(end(explode('/', $controller::getController())));
		}
		//
		// model
		return new $config['model'];
	}


	static function update(\Lemmon\Framework $controller, array $config = [])
	{
		//
		// model
		$model = self::_getModel($controller, $config);
		//
		// scaffolding
		if ($id = $controller->getRoute()->id and $item = $model->wherePrimary($id)->first())
		{
			// POST
			if ($f = $_POST)
			{
				// sanitize fields
				$f = self::_sanitize($f);
				// save
				try
				{
					$item->set($f);
					$item->save();
					$controller->getFlash()->setNotice(\Lemmon_I18n::t('Item has been updated'));
					return $controller->getRequest()->redir(($config['redir']) ?: ':section', $item);
				}
				catch (\Lemmon\Model\ValidationException $e)
				{
					$controller->getFlash()->setError($e->getMessage())
					                       ->setError(\Lemmon_I18n::t('Item has NOT been updated'))
					                       ->setErrorFields($e->getFields());
				}
			}
			// default values
			else
			{
				$controller->setData(['f' => $item]);
			}
			// model
			$controller->setData(['item' => $item]);
		}
		else
		{
			throw new \Lemmon\Http\Exception(404, \Lemmon_I18n::t('Entry not found.'));
		}
	}


	private static function _sanitize($f)
	{
		// sanitize fields
		foreach ($f as $key => $val)
		{
			if ($val)
			{
				if (is_string($val)) $f[$key] = trim($f[$key]);
				elseif (is_array($val)) $f[$key] = self::_sanitize($val);
			}
			else
			{
				$f[$key] = null;
			}
		}
		//
		return $f;
	}
}