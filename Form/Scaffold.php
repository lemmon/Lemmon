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

use \Lemmon\String as String;

/**
 * Scaffolding.
 */
class Scaffold
{


    static function paginate(\Lemmon\Model\AbstractModel &$model, $page = 0, $perpage = 25, $range = 3)
    {
        $page     = (int)$page;
        $n        = $model->count();
        $pages    = ceil($n / $perpage);
        $paginate = [
            'page'     => $page,
            'pages'    => $pages,
            'perpage'  => $perpage,
            'total'    => $n,
            'page_min' => 0,
            'page_max' => $pages - 1,
        ];
        // adjust paginator range
        if ($range) {
            $page_min = $page - $range;
            $page_max = $page + $range;
            if ($page_min < 0) {
                $page_max -= $page_min;
                $page_min = 0;
            }
            if ($page_max > $pages - 1) {
                $page_min -= $page_max - $pages + 1;
                $page_max = $pages - 1;
            }
            if ($page_min < $range) {
                $page_min = 0;
            }
            if ($page_max > $pages - $range - 1) {
                $page_max = $pages-1;
            }
            $paginate['page_min'] = $page_min;
            $paginate['page_max'] = $page_max;
        }
        // paginate sql query
        $model->limit($perpage);
        $model->offset($page * $perpage);
        //
        return $paginate;
    }


    static function index(\Lemmon\Framework $controller, array $config = [])
    {
        //
        // model
        $model = self::getModel($controller, $config);
        //
        // list
        if ($config['paginate']) {
            // paginate values
            $perpage  = (int)(($config['paginate.perpage']) ?: 25);
            $page     = (int)(($config['paginate.page']) ?: $controller->getRoute()->page);
            $range    = 3;
            // res
            $controller->setData([
                'data'     => $model,
                'paginate' => self::paginate($model, $page, $perpage, $range),
            ]);
        } else {
            // display all
            $controller->setData([
                'data' => $model,
            ]);
        }
    }


    static private function _redir($config, $item)
    {
        if ($config['redir']){
            if (is_string($config['redir'])) {
                return $config['redir'];
            } elseif (is_callable($config['redir'])) {
                return $config['redir']($item);
            }
        } else {
            return ':section';
        }
    }


    static function create(\Lemmon\Framework $controller, array $config = [], &$item = null)
    {
        //
        // model
        $model = self::getModel($controller, $config);
        $item = $model->create();
        //
        // scaffolding
        if ($item = $model->create()) {
            // model
            $controller->setData(['item' => $item]);
            // force data
            if ($config['force']) {
                $item->set($config['force']);
            }
            // POST
            if ($f = $_POST) {
                // sanitize fields
                $f = self::_sanitize($f);
                // save
                try {
                    $item->set($f);
                    $item->save();
                    $controller->getFlash()->setNotice(_t('Item has been created'));
                    return $controller->getRoute()->to(self::_redir($config, $item), $item);
                } catch (\Lemmon\Model\ValidationException $e) {
                    $controller->getFlash()->setError(_t('Your input contains errors'))
                                           ->setError(_t('Item has NOT been created'))
                                           ->setErrorFields($item->getErrors());
                }
            } elseif ($config['default'] and is_array($config['default'])) {
                // default values
                $controller->setData(['f' => $config['default']]);
            }
        }
    }


    static function getModelName(\Lemmon\Framework $controller, array &$config = [])
    {
        return $config['model'] ?: String::tableToClassName(end(explode('/', $controller::getController())));
    }


    static function getModel(\Lemmon\Framework $controller, array &$config = [])
    {
        if ($config['model'] instanceof \Lemmon\Model\AbstactModel) {
            return $config['model'];
        } else {
            $config['model'] = self::getModelName($controller, $config);
            return new $config['model'];
        }
    }


    static function update(\Lemmon\Framework $controller, array $config = [])
    {
        //
        // model
        $model = self::getModel($controller, $config);
        //
        // scaffolding
        if ($id = $controller->getRoute()->id and $item = $model->wherePrimary($id)->first()) {
            // model
            $controller->setData(['item' => $item]);
            // force data
            if ($config['force']) {
                $item->set($config['force']);
            }
            // on POST
            if ($f = $_POST) {
                // sanitize fields
                $f = self::_sanitize($f);
                // save
                try {
                    $item->set($f);
                    $item->save();
                    $controller->getFlash()->setNotice(_t('Item has been updated'));
                    return $controller->getRoute()->to(self::_redir($config, $item), $item);
                } catch (\Lemmon\Model\ValidationException $e) {
                    // error saving property
                    $controller->getFlash()->setError(_t('Your input contains errors'))
                                           ->setError(_t('Item has NOT been updated'))
                                           ->setErrorFields($item->getErrors());
                }
            } else {
                // default values
                $controller->setData(['f' => $item]);
            }
        } else {
            die('Scaffold: Entry not found.');
        }
    }


    private static function _sanitize($f)
    {
        // sanitize fields
        foreach ($f as $key => $val) {
            if ($val) {
                if (is_string($val)) $f[$key] = trim($f[$key]);
                elseif (is_array($val)) $f[$key] = self::_sanitize($val);
            }
            else {
                $f[$key] = null;
            }
        }
        //
        return $f;
    }
}
