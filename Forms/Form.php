<?php

namespace Lemmon\Forms;

use \Lemmon\Http\Request;

class Form implements \JsonSerializable
{
    const VALIDATE_TOKEN = 1;

    // sanitization
    const TRIM_STRINGS  = 0b1;
    const EMPTY_TO_NULL = 0b10;
    const REMOVE_EMPTY  = 0b100;
    const KEEP_ROOT     = 0b1000;

    // validation
    const REQUIRED =  0b1; // TODO
    const UNIQUE   = 0b10; // TODO

    protected $sanitize;

    private $_token;
    private $_method;
    private $_model;
    private $_submitted = FALSE;
    private $_fields = [];
    private $_values = [];
    private $_errors = [];
    private $_error;
    private $_onValidate = [];
    private $_onSuccess;
    private $_onError;


    function __construct($item = NULL)
    {
        if ($item) {
            if ($item instanceof \Lemmon\Db\AbstractModel) {
                $this->_model = $item;
                $this->_values = $item->getData();
                if (is_callable([$item, '__validate'])) {
                    $item->__validate($this);
                }
            }
        }
    }


    function __get($name)
    {
        return $this->_values[$name];
    }


    function __isset($name)
    {
        return array_key_exists($name, $this->_values);
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


    function addField($name, ...$args)
    {
        $this->_fields[$name] = [
            'name'     => $name,
            'required' => isset($args[0]) and is_bool($args[0]) ? array_shift($args) : FALSE,
            'rule'     => array_key_exists(0, $args) ? array_shift($args) : NULL,
            'sanitize' => array_key_exists(0, $args) ? array_shift($args) : NULL,
        ];
        return $this;
    }


    function setDefaultValue($field, $value)
    {
        if ($field = explode('.', $field) and !$this->_get($field, $this->_values)) {
            $this->_set($field, $value, $this->_values);
        }
        return $this;
    }


    function getField($name)
    {
        $name = strtr($name, ['[' => '.', ']' => '']);
        return array_key_exists($name, $this->_fields) ? [
            'name' => $this->_fields[$name]['name'],
            'value' => $this->_get(explode('.', $name), $this->_values),
            'error' => $this->_get(explode('.', $name), $this->_errors),
        ] : NULL;
    }


    function getValues()
    {
        return $this->_values;
    }


    function onValidate($callback)
    {
        if (!is_callable($callback)) {
            throw new \Exception('Invalid callback');
        }
        $this->_onValidate[] = $callback;
        return $this;
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
        return (TRUE === $this->_submitted) and empty($this->_errors) and (TRUE !== $this->_error);
    }


    function setError($error, $type = NULL)
    {
        if (NULL === $type) {
            // flash error
            $this->_errors['_flash'][] = $error;
        } else {
            // field error
            if (is_array($type)) {
                $this->_errors[$error] += $type;
            } elseif (is_bool($type)) {
                if (empty($this->_errors[$error])) $this->_errors[$error] = $type;
            } else {
                $this->_errors[$error][] = $type;
            }
        }
        return $this;
    }


    private function _get($field, $values)
    {
        return isset($values[$field[0]]) ? (count($field) > 1 ? $this->_get(array_slice($field, 1), $values[$field[0]]) : $values[$field[0]]) : NULL;
    }


    private function _set($field, $value, &$values)
    {
        if (count($field) > 1) {
            $this->_set(array_slice($field, 1), $value, $values[$field[0]]);
        } else {
            $values[$field[0]] = $value;
        }
    }


    function sanitize($flags)
    {
        $this->sanitize = $flags;
        return $this;
    }


    function validate(array $data, $flags = 0)
    {
        $this->_errors = [];
        $this->_error = NULL;
        // run
        if ((NULL === $this->_method or $_SERVER['REQUEST_METHOD'] == $this->_method) and $data) {
            // auth token
            if (isset($data['auth_token'])) {
                $this->_setToken($data['auth_token']);
            }
            // submitted
            $this->_submitted = TRUE;
            $f = [];
            // sanitize
            if ($this->sanitize) {
                $data = $this->_sanitize($data, $this->sanitize);
            }
            // set data
            foreach ($this->_fields as $name => $field) {
                $this->_set(explode('.', $name), $this->_get(explode('.', $name), $data), $this->_values);
            }
            // validate
            foreach ($this->_fields as $name => $field) {
                $error = NULL;
                $val = $this->_get(explode('.', $name), $data);
                // sanitize
                if (is_callable($field['sanitize'])) {
                    $val = $field['sanitize']($val);
                }
                if (FALSE !== $val) {
                    // general validation
                    if ($field['required'] & self::REQUIRED and empty($val)) {
                        $error['required'] = 'Field is required';
                    } elseif (is_callable($field['rule'])) {
                        $_err = [];
                        if (FALSE === call_user_func_array($field['rule'], [&$val, &$_err]) or $_err) {
                            $error = $_err;
                            $error[] = 'Invalid value';
                        }
                    } elseif ($_rule = $field['rule'] and (is_string($_rule) or (is_array($_rule) and $_rule = @$_rule['rule']))) {
                        if ($_rule{0} == '/') {
                            if (!preg_match($_rule, $val)) {
                                $error[] = 'Invalid value';
                            }
                        } else {
                            $_rule = explode('|', $_rule);
                            if (strlen($val)) {
                                switch ($_rule[0]) {
                                    case 'bool':
                                        $val = $val ? TRUE : FALSE;
                                        break;
                                    case 'email':
                                        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) $error[$_rule[0]] = 'Invalid email';
                                        break;
                                    case 'url':
                                        if (!filter_var($val, FILTER_VALIDATE_URL)) $error[$_rule[0]] = 'Invalid URL';
                                        break;
                                    case 'float':
                                        $val = str_replace(',', '.', $val);
                                        if (preg_match('/^\-?\d+(\.\d*)?$/', $val)) {
                                            $val = floatval($val);
                                        } else {
                                            $error[$_rule[0]] = 'Invalid value';
                                        }
                                        break;
                                    case 'int':
                                        if (preg_match('/^\-?\d+$/', $val)) {
                                            $val = intval($val);
                                        } else {
                                            $error[$_rule[0]] = 'Invalid value';
                                        }
                                        break;
                                    case 'any':
                                        break;
                                    default:
                                        throw new \Exception(sprintf('Unknown rule %s', $_rule[0]));
                                        break;
                                }
                            }
                            if (strlen($val) == 0) {
                                if (isset($_rule[1])) {
                                    switch ($_rule[1]) {
                                        case 'false': $val = FALSE; break;
                                        case 'null':  $val = NULL; break;
                                    }
                                } else {
                                    $val = NULL;
                                }
                            }
                        }
                    }
                    if (!$error and is_array($field['rule']) and isset($field['rule']['test']) and is_callable($field['rule']['test'])) {
                        $_err = [];
                        if (FALSE === call_user_func_array($field['rule']['test'], [&$val, &$_err]) or $_err) {
                            $error = $_err;
                            $error[] = 'Invalid value';
                        }
                    }
                    // extended validation
                    if (is_array($field['rule']) and $_rule = $field['rule']) {
                        if (isset($_rule['min']) and $val < $_rule['min']) $error['min'] = 'Invalid value';
                        if (isset($_rule['max']) and $val > $_rule['max']) $error['max'] = 'Invalid value';
                        if (isset($_rule['min-length']) and strlen($val) < $_rule['min-length']) $error['min-length'] = 'Invalid value';
                        if (isset($_rule['max-length']) and strlen($val) > $_rule['max-length']) $error['max-length'] = 'Invalid value';
                    }
                    // unique TODO
                    if (is_int($field['required']) and empty($error)) {
                    }
                    //
                    $this->_set(explode('.', $name), $val, $this->_values);
                    if ($error) {
                        $this->_set(explode('.', $name), $error, $this->_errors);
                    }
                }
            }
            // values
            $f = $this->_values;
            // on validate event
            if ($this->isValid()) {
                foreach ($this->_onValidate as $e) {
                    if (FALSE === $e($this, $f, $this->_model)) {
                        $this->_error = TRUE;
                        break;
                    }
                }
            }
            // final events
            if ($this->isValid()) {
                if ($e = $this->_onSuccess and $res = $e($this, $f, $this->_model)) return $res;
            } else {
                if ($e = $this->_onError and $res = $e($this, $f, $this->_model)) return $res;
            }
            /*
                header('Cache-Control: no-cache, must-revalidate');
                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            */
        }
        return $this;
    }


    function toArray()
    {
        $res = [];
        if ($this->_submitted) {
            $res['valid'] = $this->isValid();
            foreach (array_keys($this->_fields) as $_field) {
                if ($field = $this->getField($_field) and @$field['error']) {
                    $res['fields'][$_field] = [
                        'error' => $field['error'],
                    ];
                }
            }
        }
        return $res;
    }


    function jsonSerialize()
    {
        return $this->toArray();
    }
 
 
    private static function _sanitize($data, $flags, $level = 0)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if ($res = self::_sanitize($val, $flags, $level + 1) or !($flags & self::REMOVE_EMPTY) or (0 == $level and $flags & self::KEEP_ROOT)) {
                    $data[$key] = $res;
                } else {
                    unset($data[$key]);
                }
            }
        } elseif (is_string($data)) {
            if ($flags & self::TRIM_STRINGS) {
                $data = trim($data);
            }
        }
        return ($data or !($flags & self::EMPTY_TO_NULL)) ? $data : NULL;
    }
}