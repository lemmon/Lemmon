<?php

namespace Lemmon\Db;

class SimpleModelContainer implements \Iterator
{
    private $_collection;
    private $_cursor;
    private $_model;


    function __construct(AbstractCollection $collection, \MongoCursor $cursor, $model)
    {
        $this->_collection = $collection;
        $this->_cursor = $cursor;
        $this->_model = $model;
        // default sort
        if (isset($model) and isset($model::$sort)) {
            $this->sort($model::$sort);
        }
    }


    function toArray()
    {
        return iterator_to_array($this);
    }


    public function current()
    {
        return new $this->_model($this->_collection, $this->_cursor->current());
    }


    public function key()
    {
        return $this->_cursor->key();
    }


    public function next()
    {
        return $this->_cursor->next();
    }


    public function rewind()
    {
        return $this->_cursor->rewind();
    }


    public function valid()
    {
        return $this->_cursor->valid();
    }


    public function count()
    {
        return $this->_cursor->count();
    }


    public function sort($_sort)
    {
        if (func_num_args() > 1) {
            $_sort = func_get_args();
        } elseif (is_string($_sort)) {
            $_sort = explode(',', $_sort);
        } elseif (!is_array($_sort)) {
            throw new \Exception('Invalid type');
        }
        $sort = [];
        foreach ($_sort as $key => $val) {
            if (is_int($key)) {
                if ('-' == $val{0}) {
                    $sort[substr($val, 1)] = -1;
                } else {
                    $sort[$val] = 1;
                }
            }
        }
        $this->_cursor->sort($sort);
        return $this;
    }


    public function skip($n)
    {
        $this->_cursor->skip($n);
        return $this;
    }


    public function limit($n)
    {
        $this->_cursor->limit($n);
        return $this;
    }
}
