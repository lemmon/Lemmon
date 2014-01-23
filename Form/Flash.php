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
 * Flash notices.
 */
class Flash
{
    private $_route;
    private $_link;
    private $_messages = [];
    private $_fields = [];


    function __construct(\Lemmon\Route $route)
    {
        // route
        $this->_route = $route;
        // links stored in sessions
        if ($flash = $_SESSION['__FLASH_MESSAGES__']) {
            $this->_messages = $flash['messages'];
            $this->_fields = $flash['fields'];
            unset($_SESSION['__FLASH_MESSAGES__']);
        }
    }


    function __destruct()
    {
        if (!$this->_link) {
            $_SESSION['__FLASH_MESSAGES__'] = [
                'messages' => $this->_messages,
                'fields'   => $this->_fields,
            ];
        }
    }


    function assignNewLink() {} // [depreciated] legacy fnc


    function setError($message)
    {
        $this->_messages['error'][] = $message;
        return $this;
    }


    function setErrorField($field, $message = '', $case = null)
    {
        if ($case) {
            $this->_fields[$field][$case] = $message;
        } else {
            $this->_fields[$field][] = $message;
        }
        return $this;
    }


    function setErrorFields(array $fields)
    {
        foreach ($fields as $field => $errors) {
            if (is_array($errors)) {
                foreach ($errors as $case => $message) {
                    if (is_int($case)) {
                        $this->setErrorField($case, $message);
                    } else {
                        $this->setErrorField($field, $message, $case);
                    }
                }
            } elseif (is_int($field)) {
                $this->setErrorField($errors);
            } else {
                $this->setErrorField($field, $errors);
            }
        }
        return $this;
    }


    function setNotice($message)
    {
        $this->_messages['notice'][] = $message;
        return $this;
    }


    function getNotices()
    {
        $this->_assignLink();
        return $this->_messages['notice'];
    }


    function getErrors()
    {
        if ($res = $this->_messages['error']){
            $this->_assignLink();
            return $res;
        }
    }


    function getFields()
    {
        return $this->_fields;
    }


    private function _assignLink()
    {
        if (!$this->_link) {
            $this->_link = ($_POST ? microtime(true) : '') . '@' . (string)$this->_route->getSelf();
        }
    }
}
