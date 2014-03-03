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

use \Lemmon\Db\Adapter as DbAdapter,
    \Lemmon\Sql\Expression as SqlExpression,
    \Lemmon\Model\AbstractModel,
    \Lemmon\String as String;

/**
 * Model.
 */
abstract class AbstractRow /*implements \ArrayAccess*/
{
    const STATE_EMPTY    = 0b000; //  0
    const STATE_NEW      = 0b001; //  1
    const STATE_LOADED   = 0b010; //  2
    const STATE_MODIFIED = 0b011; //  3
    const STATE_CREATED  = 0b100; //  4
    const STATE_UPDATED  = 0b110; //  6
    
    static protected $model;

    protected $data = [];
    protected $dataDefault = [];

    private $_adapter;
    private $_schema;
    private $_state;

    private $_errors = [];


    final function __construct(array $data = null, $adapter = null, AbstractModel $model = null, $from_db = false)
    {
        if (!isset(static::$model)) throw new \Exception('No model has been defined.');
        
        // model
        $this->_schema = $model ? $model->getSchema() : Schema::factory(static::$model);
        
        // adapter
        if ($adapter instanceof DbAdapter) {
            $this->_adapter = $adapter;
        } elseif ($model) {
            $this->_adapter = $model->getAdapter();
        } elseif (is_string($adapter)) {
            $this->_adapter = DbAdapter::get($adapter);
        } else {
            $this->_adapter = DbAdapter::getDefault();
        }
        
        // data
        if ($data) {
            if (is_array($data)) {
                $this->dataDefault = $data;
                $this->set($data);
                if ($from_db) {
                    $this->_state = self::STATE_LOADED;
                } else {
                    $this->_state = self::STATE_NEW;
                }
            }
        } else {
            $this->_state = self::STATE_EMPTY;
        }
        
        // init
        $this->__init();
    }


    protected function __init(){}
    protected function onValidate(){}
    protected function onBeforeCreate(){}
    protected function onBeforeUpdate(){}
    protected function onAfterCreate(){}
    protected function onAfterUpdate(){}
    protected function upload(){}


    static function find($cond, DbAdapter $adapter = null)
    {
        if (!isset(static::$model)) throw new \Exception('No model has been defined.');
        return call_user_func([static::$model, 'find'], $cond, $adapter)->first();
    }


    static function getModelName()
    {
        if (!isset(static::$model))
            throw new \Exception('No model has been defined.');
        
        return static::$model;
    }


    private function _sanitize(&$f)
    {
        // timestamps
        if (is_array($ts = $this->_schema->get('timestamp'))) {
            if (isset($ts[0]) and !isset($f[$ts[0]])) $f[$ts[0]] = new SqlExpression('NOW()');
            if (isset($ts[1])) $f[$ts[1]] = new SqlExpression('NOW()');
        }
        //
        return;
    }


    final protected function createQuery($type)
    {
        return call_user_func([$this->_adapter->query(), $type], $this->_schema->get('table'));
    }


    final protected function getSchema()
    {
        return $this->_schema;
    }


    private function _clearErrors()
    {
        $this->_errors = [];
    }


    final protected function setError($field, $message = '', $case = null)
    {
        if ($case) {
            $this->_errors[$field][$case] = $message;
        } else {
            $this->_errors[$field][] = $message;
        }
        return $this;
    }


    final function getErrors()
    {
        return $this->_errors;
    }


    final protected function _isInvalid()
    {
        return $this->_errors ? true : false;
    }


    final function isValid()
    {
        try {
            // validate
            $this->_validate($this->data);
            // ok
            return true;
        } catch (ValidationException $e) {
            // input is invalid
            return false;
        }
    }


    final function isInvalid()
    {
        return !$this->isValid();
    }


    private function _validate(&$f, &$to_upload = null)
    {
        $ok = true;
        $this->_clearErrors();
        // required fields
        if (is_array($r = $this->_schema->required)) {
            foreach ($r as $field => $condition) {
                switch ($condition) {
                    case 'required':
                        if (empty($f[$field]))
                            $this->setError($field, _t('This field is required'), $condition);
                        break;
                    case 'allow_null':
                        if (!array_key_exists($field, $f))
                            $this->setError($field, _t('This field is required'), $condition);
                        break;
                    case 'upload':
                        if ((!array_key_exists($field, $_FILES) or $_FILES[$field]['error'] != UPLOAD_ERR_OK) and !isset($f[$field]))
                            $this->setError($field, _t('Error uploading file'), $condition);
                        break;
                    default:
                        throw new \Exception(_t('Unknown flag `%s` on field `%s`.', $condition, $field));
                        break;
                }
            }
        }
        // uploads
        if ($uploads = $this->_schema->uploads and $base_dir = $this->_schema->uploadDir) {
            $to_upload = [];
            // uploads
            foreach ($_FILES as $field => $file) {
                $upload_dir = strftime($uploads[$field]);
                // upload dir
                $_dir = $base_dir . ($upload_dir ? '/' . $upload_dir : '');
                if (!($_dir and (is_dir($_dir) or @mkdir($_dir, 0777, true)) and is_writable($_dir))) {
                    throw new \Exception(_t('Invalid upload dir %s.', $_dir));
                }
                // upload
                if ($file['error'] == UPLOAD_ERR_OK) {
                    $_file = [
                        'base' => substr($file['name'], 0, strrpos($file['name'], '.')),
                        'ext'  => substr($file['name'], strrpos($file['name'], '.') + 1),
                    ];
                    $file_name = String::asciize($_file['base']) . '.' . time() . '.' . strtolower($_file['ext']);
                    $file = $_dir . '/' . $file_name;
                    $f[$field] = ($upload_dir ? $upload_dir . '/' : '') . $file_name;
                    $to_upload[$field] = [
                        'dir' => $base_dir,
                        'file' => ($upload_dir ? $upload_dir . '/' : '') . $file_name,
                        '_old' => $this->data[$field],
                    ];
                    if (file_exists($file)) {
                        throw new \Exception(_t('File %s already exists at provided location.', $file));
                    }
                } elseif ($file['error'] == UPLOAD_ERR_NO_FILE) {
                    // upload stays the same
                } else {
                    throw new \Exception(_t('File upload error no #%d.', $file['error']));
                }
            }
        }
        //
        if ($this->onValidate($f) === false or $this->_isInvalid()) {
            throw new ValidationException();
        }
    }


    private function _uploads($to_upload, &$f)
    {
        if ($to_upload) {
            foreach ($to_upload as $field => $upload) {
                $file = $_FILES[$field];
                // old file
                if ($upload['_old']) @unlink($upload['dir'] . '/' . $upload['_old']);
                unset($upload['_old']);
                $file_fullpath = join('/', $upload);
                // new file
                if (move_uploaded_file($file['tmp_name'], $file_fullpath)) {
                    $upload = $upload['file'];
                    $this->upload($field, $file_fullpath, $f);
                } else {
                    throw new \Exception(sprintf('Unknown error occured when moving uploaded %s (%s).', $field, $upload));
                }
            }
        }
    }


    final function _getState()
    {
        return $this->_state;
    }


    function save($force = false)
    {
        // data
        $data = $this->data;
        // validate
        if ($force or ($this->_state & 0b1 and $this->_validate($data, $to_upload) !== false and $this->_uploads($to_upload, $data) !== false)) {
            // sanitize data
            $this->_sanitize($data);
            // before create/update event
            ($this->_state & 0b10) ? $this->onBeforeUpdate($data) : $this->onBeforeCreate($data);
            // query
            $q = new \Lemmon\Sql\Replace(DbAdapter::getDefault()->query(), $this->_schema->get('table'));
            // set values
            $q->set($data);
            // execute
            $q->exec();
            // insert id
            if ($id = $q->getInsertId()) {
                $this->data[$this->_schema->get('primary')[0]] = $id;
            }
            // after create/update event
            ($this->_state & 0b10) ? $this->onAfterUpdate($data) : $this->onAfterCreate($data);
            // state
            $this->_state |= 0b100; // needs to reload
            $this->_state &= ~0b1;  // not modified
        }
        //
        return $this;
    }


    function set(array $data)
    {
        $this->reload();
        foreach ($data as $field => $value) $this->_set($field, $value);
        $this->requireSave();
        return $this;
    }


    protected function requireSave()
    {
        $this->_state |= 0b1;
    }


    function toArray()
    {
        $this->reload();
        return $this->data;
    }


    function __isset($key)
    {
        return array_key_exists($key, $this->data) || method_exists($this, $method = 'get' . $key);
    }


    private function _getPrimaryData()
    {
        return array_intersect_key($this->data, array_flip($this->_schema->primary));
    }


    final function reload($force = false)
    {
        if ($force or ($this->_state & 0b100)) {
            // data needs to be reloaded
            $this->dataDefault = $this->data;
            $this->data = (array)(new \Lemmon\Sql\Select(DbAdapter::getDefault()->query(), $this->_schema->get('table')))->where($this->_getPrimaryData())->first();
            $this->_state = self::STATE_LOADED;
        }
        return $this;
    }


    function __get($key)
    {
        $this->reload();
        if (method_exists($this, $method = 'get' . $key)) {
            return $this->{$method}();
        }
        /*
        elseif (array_key_exists($key, $this->_schema->uploads))
        {
            return new \Lemmon\Files\File($this->_schema->uploadDir . '/' . $this->data[$key]);
        }
        */
        else {
            return $this->data[$key];
        }
    }


    function __set($key, $val)
    {
        /*
        self::offsetSet($key, $val);
        */
        $this->reload();
        $this->_set($key, $val);
        $this->_state |= 0b1;
    }


    private function _set($key, $val)
    {
        $this->data[$key] = $val;
    }


    function getDefault($key)
    {
        return $this->dataDefault[$key];
    }
}
