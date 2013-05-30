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
    \Lemmon_I18N as I18n,
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

    private $_schema;
    public $_state;


    final function __construct($data = null)
    {
        if (!isset(static::$model)) throw new \Exception('No model has been defined.');
    
        // model
        $this->_schema = Schema::factory(static::$model);
        
        // data
        if ($data)
        {
            if (is_array($data))
            {
                $this->dataDefault = $data;
                $this->set($data);
                $this->_state = self::STATE_LOADED;
            }
        }
        else
        {
            $this->_state = self::STATE_EMPTY;
        }
    }


    protected function onValidate(){}
    protected function onBeforeCreate(){}
    protected function onBeforeUpdate(){}
    protected function onAfterCreate(){}
    protected function onAfterUpdate(){}
    protected function upload(){}


    static function find($cond)
    {
        if (!isset(static::$model)) throw new \Exception('No model has been defined.');
        return call_user_func([static::$model, 'find'], $cond)->first();
    }


    private function _sanitize(&$f)
    {
        // timestamps
        if (is_array($ts = $this->_schema->get('timestamp')))
        {
            if (isset($ts[0]) and !isset($f[$ts[0]])) $f[$ts[0]] = new SqlExpression('NOW()');
            if (isset($ts[1])) $f[$ts[1]] = new SqlExpression('NOW()');
        }
        //
        return;
    }


    private function _validate(&$f, &$to_upload)
    {
        // required fields
        if (is_array($r = $this->_schema->get('required')))
        {
            $fields = [];
            foreach ($r as $field => $condition)
            {
                switch ($condition)
                {
                    case 'required':
                        if (!isset($f[$field])) $fields[$field] = I18n::t(String::human($field));
                        break;
                    case 'allow_null':
                        if (!array_key_exists($field, $f)) $fields[$field] = I18n::t(String::human($field));
                        break;
                    case 'upload':
                        if ((!array_key_exists($field, $_FILES) or $_FILES[$field]['error'] != UPLOAD_ERR_OK)
                            and !isset($f[$field])) $fields[$field] = I18n::t(String::human($field));
                        break;
                    default:
                        throw new \Exception(sprintf('Unknown flag `%s` on field `%s`.', $condition, $field));
                        break;
                }
            }
            if ($fields)
            {
                throw new ValidationException(I18n::tn('Missing field %2$s', 'Missing %d fields (%s)', count($fields), join(', ', $fields)), array_keys($fields));
            }
        }
        // uploads
        if ($uploads = $this->_schema->uploads and $base_dir = $this->_schema->uploadDir)
        {
            $to_upload = [];
            // uploads
            foreach ($_FILES as $field => $file)
            {
                $upload_dir = strftime($uploads[$field]);
                // upload dir
                $_dir = $base_dir . ($upload_dir ? '/' . $upload_dir : '');
                if (!($_dir and (is_dir($_dir) or @mkdir($_dir, 0777, true)) and is_writable($_dir)))
                {
                    throw new \Exception(sprintf('Invalid upload dir %s.', $_dir));
                }
                // upload
                if ($file['error'] == UPLOAD_ERR_OK)
                {
                    $_file = [
                        'base' => substr($file['name'], 0, strrpos($file['name'], '.')),
                        'ext'  => substr($file['name'], strrpos($file['name'], '.') + 1),
                    ];
                    $file_name = String::asciize($_file['base']) . '.' . time() . '.' . $_file['ext'];
                    $file = $_dir . '/' . $file_name;
                    $f[$field] = ($upload_dir ? $upload_dir . '/' : '') . $file_name;
                    $to_upload[$field] = [
                        'dir' => $base_dir,
                        'file' => ($upload_dir ? $upload_dir . '/' : '') . $file_name,
                        '_old' => $this->data[$field],
                    ];
                    if (file_exists($file))
                    {
                        throw new \Exception(sprintf('File %s already exists at provided location.', $file));
                    }
                }
                elseif ($file['error'] == UPLOAD_ERR_NO_FILE)
                {
                    // upload stays the same
                }
                else
                {
                    throw new \Exception(sprintf('File upload error no #%d.', $file['error']));
                }
            }
        }
        // user defined validation
        $_msg = '';
        $_fields = [];
        if ($this->onValidate($f, $_msg, $_fields) === false)
        {
            throw new ValidationException($_msg, $_fields);
        }
        //
        return;
    }


    private function _uploads($to_upload, &$f)
    {
        if ($to_upload)
        {
            foreach ($to_upload as $field => $upload)
            {
                $file = $_FILES[$field];
                // old file
                if ($upload['_old']) @unlink($upload['dir'] . '/' . $upload['_old']);
                unset($upload['_old']);
                $file_fullpath = join('/', $upload);
                // new file
                if (move_uploaded_file($file['tmp_name'], $file_fullpath))
                {
                    $upload = $upload['file'];
                    $this->upload($field, $file_fullpath, $f);
                }
                else
                {
                    throw new \Exception(sprintf('Unknown error occured when moving uploaded %s (%s).', $field, $upload));
                }
            }
        }
    }


    function save($force = false)
    {
        // data
        $data = $this->data;
        // validate
        if ($force or ($this->_sanitize($data) !== false and $this->_validate($data, $to_upload) !== false and $this->_uploads($to_upload, $data) !== false))
        {
            // before create/update event
            ($this->_state & 0b10) ? $this->onBeforeUpdate($data) : $this->onBeforeCreate($data);
            // query
            $q = new \Lemmon\Sql\Replace(DbAdapter::getDefault()->query(), $this->_schema->get('table'));
            // set values
            $q->set($data);
            // execute
            $q->exec();
            // insert id
            if ($id = $q->getInsertId())
            {
                $this->data[$this->_schema->get('primary')[0]] = $id;
            }
            // after create/update event
            ($this->_state & 0b10) ? $this->onAfterUpdate($data) : $this->onAfterCreate($data);
            // state
            $this->_state |= 0b100; // needs to reload
            $this->_state ^= 0b1;   // not modified
        }
        //
        return $this;
    }


    function set($data)
    {
        $this->reload();
        foreach ($data as $field => $value) $this->_set($field, $value);
        $this->_state |= 0b1;
        return $this;
    }


    function getData()
    {
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


    final function reload()
    {
        if ($this->_state & 0b100)
        {
            $this->dataDefault = $this->data;
            $this->data = (array)(new \Lemmon\Sql\Select(DbAdapter::getDefault()->query(), $this->_schema->get('table')))->where($this->_getPrimaryData())->first();
            $this->_state = self::STATE_LOADED;
        }
    }


    function __get($key)
    {
        $this->reload();
        if (method_exists($this, $method = 'get' . $key))
        {
            return $this->{$method}();
        }
        /*
        elseif (array_key_exists($key, $this->_schema->uploads))
        {
            return new \Lemmon\Files\File($this->_schema->uploadDir . '/' . $this->data[$key]);
        }
        */
        else
        {
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


    /*
    function offsetExists($key)
    {
        $this->reload();
        return array_key_exists($key, $this->data);
    }


    function offsetGet($key)
    {
        $this->reload();
        return $this->data[$key];
    }


    function offsetSet($key, $val)
    {
        $this->reload();
        $this->_set($key, $val);
        $this->_state |= 0b1;
    }


    function offsetUnset($key)
    {
        $this->reload();
        unset($this->data[$key]);
        $this->_state |= 0b1;
    }
    */


    private function _set($key, $val)
    {
        $this->data[$key] = $val;
    }


    function getDefault($key)
    {
        return $this->dataDefault[$key];
    }
}
