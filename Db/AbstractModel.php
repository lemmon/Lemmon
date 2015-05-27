<?php

namespace Lemmon\Db;

abstract class AbstractModel
{
    const STATE_EMPTY    =   0b0; // 0
    const STATE_NEW      =   0b1; // 1
    const STATE_LOADED   =  0b10; // 2
    const STATE_MODIFIED =  0b11; // 3
    const STATE_CREATED  = 0b100; // 4
    const STATE_UPDATED  = 0b110; // 6
    
    const FILTER_REMOVE_EMPTY = 0b1;
    const FILTER_REMOVE_NULL  = 0b10;

    static $table;
    static $sort;
    static $timestamp;

    private $_collection;
    private $_data = [];
    private $_state;


    final function __construct(AbstractCollection $collection, array $data = [])
    {
        $this->_collection = $collection;
        $this->_data = $data;
        $this->_state = $data ? self::STATE_LOADED : self::STATE_EMPTY;
    }


    function __get($name)
    {
        $name = explode('.', $name);
        return $this->_get($this->_data, $name);
    }


    function __isset($name)
    {
        return isset($this->_data[$name]);
    }


    function __set($name, $value)
    {
        $name = explode('.', $name);
        $this->_data = $this->_set($this->_data, $name, $value);
        $this->_state |= self::STATE_NEW;
    }


    private function _get($data, $name)
    {
        if (count($name) > 1) {
            return $this->_get(@$data[$name[0]], array_slice($name, 1));
        } else {
            return $data[$name[0]];
        }
    }


    private function _set($data, $name, $value)
    {
        if (count($name) > 1) {
            $data[$name[0]] = $this->_set(@$data[$name[0]], array_slice($name, 1), $value);
        } else {
            $data[$name[0]] = $value;
        }
        return $data;
    }


    function onBeforeSave(&$f) {}
    function onBeforeCreate(&$f) {}
    function onBeforeUpdate(&$f) {}


    /*
    public function db($name = NULL, $query = NULL)
    {
        return $db::db($this->getConnection(), $name, $query);
    }
    */


    public function db($name, $query = [])
    {
        return $this->_collection->getConnection()->find($name, $query);
    }


    public function getCollection()
    {
        return $this->_collection;
    }


    public function getData()
    {
        return $this->_data;
    }


    public function setData(array $data)
    {
        $this->_data = array_replace_recursive($this->_data, $data);
        return $this;
    }


    public function save(array $data = [], $flags = NULL)
    {
        $data = array_replace_recursive($this->_data, $data);
        // timestamps
        if ($_field = @static::$timestamp[0] and !($this->_state & self::STATE_LOADED)) {
            $data[$_field] = new \MongoDate;
        }
        if ($_field = @static::$timestamp[1]) {
            $data[$_field] = new \MongoDate;
        }
        // events
        $this->onBeforeSave($data);
        ($this->_state & self::STATE_LOADED) ? $this->onBeforeUpdate($data) : $this->onBeforeCreate($data);
        // filters
        if ($flags & self::FILTER_REMOVE_EMPTY) {
            $data = array_filter_recursive($data);
        }
        if ($flags & self::FILTER_REMOVE_NULL) {
            $data = array_filter_recursive($data, function($a){ return NULL !== $a; });
        }
        // restrict
        if (defined(get_class($this) . '::RESTRICT')) {
            $data = array_merge($data, static::RESTRICT);
        }
        // save
        $res = $this->_collection->getCollection()->save($this->_data = $data);
        // apply new state
        #$this->_state |= self::STATE_CREATED; // needs to reload
        #$this->_state &= ~self::STATE_NEW;  // not modified
        $this->_state = self::STATE_LOADED;
        return $this;
    }


    public function delete()
    {
        $this->_collection->getCollection()->remove(['_id' => $this->_data['_id']], ['justOne' => TRUE]);
        $this->_state = self::STATE_EMPTY;
        return $this;
    }
}

//
// functions
function array_filter_recursive(array $array, \Closure $callback = NULL)
{
    foreach ($array as $i => &$item) {
        if (is_array($item)) $item = array_filter_recursive($item, $callback);
        if ((NULL === $callback and empty($item)) or ($callback and FALSE === $callback($item))) { unset($array[$i]); }
    }
    return $array;
}
