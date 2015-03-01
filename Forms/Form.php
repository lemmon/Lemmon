<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Forms;

/**
 * @author Jakub Pelák <jpelak@gmail.com>
 */
class Form extends AbstractForm
{
    const VALIDATE_TOKEN = 1;

    private $_token;
    private $_method = 'POST';
    private $_fields = [];
    private $_values = [];
    private $_errors = [];
    private $_onSuccess;
    private $_onError;


    function __construct()
    {
    }


    function getToken()
    {
        return $this->_token ?: $this->_setToken();
    }


    private function _setToken($token = null)
    {
        if (empty($token)) {
            $token = sha1(uniqid(microtime(true)));
        }
        $this->_token = $token;
        $_SESSION['form_tokens'][] = $token;
        if (count($_SESSION['form_tokens']) > 33) {
            $_SESSION['form_tokens'] = array_slice($_SESSION['form_tokens'], -33);
        }
        return $token;
    }


    function addField($name, $required = false, $rule = null)
    {
        $this->_fields[$name] = [
            'name' => $name,
            'required' => $required,
            'rule' => $rule,
        ];
        return $this;
    }


    function getField($name)
    {
        return array_merge($this->_fields[$name], [
            'value' => $this->_values[$name],
            'error' => $this->_errors[$name],
        ]);
    }


    function onSuccess($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Invalid callback');
        }
        $this->_onSuccess = $callback;
        return $this;
    }


    function onError($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Invalid callback');
        }
        $this->_onError = $callback;
        return $this;
    }


    function isValid()
    {
        return empty($this->_errors);
    }


    function validate(array $data, $flags = 0)
    {
        if ($_SERVER['REQUEST_METHOD'] == $this->_method and $data) {
            // auth token
            if (isset($data['auth_token'])) {
                $this->_setToken($data['auth_token']);
            }
            // sanitize
            $data = self::_sanitize($data);
            // validate
            foreach ($this->_fields as $name => $field) {
                $val = $data[$name];
                if ($this->_fields[$name]['required'] and empty($val)) {
                    $this->_errors[$name]['required'] = 'Field is required';
                } elseif ($val and $_rule = $field['rule']) {
                    if (is_callable($_rule)) {
                        
                    } else {
                        switch ($_rule) {
                            case 'email':
                                if (!filter_var($val, FILTER_VALIDATE_EMAIL)) $this->_errors[$name][$_rule] = 'Invalid email';
                                break;
                            default:
                                throw new \Exception(sprintf('Unknown rule %s', $_rule));
                                break;
                        }
                    }
                }
                $this->_values[$name] = $val;
            }
            //
            if ($this->isValid()) {
                if (is_callable($this->_onSuccess)) {
                    $e = $this->_onSuccess;
                    $e($this, $this->_values);
                }
            } else {
                if (is_callable($this->_onError)) {
                    $e = $this->_onError;
                    $e($this, $this->_values);
                }
            }
            // ajax
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                // return JSON
                $res = ['valid' => $this->isValid()];
                foreach (array_keys($this->_fields) as $_field) {
                    $res['fields'][$_field] = $this->getField($_field);
                }
                header('Content-Type: application/json');
                echo json_encode($res, JSON_PRETTY_PRINT);
                die;
            }
        }
        return $this;
    }


    private static function _sanitize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if ($res = self::_sanitize($val)) {
                    $data[$key] = $res;
                }
            }
        } elseif (is_string($data)) {
            $data = trim($data);
        }
        return $data;
    }
}