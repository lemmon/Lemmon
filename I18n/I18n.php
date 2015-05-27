<?php

namespace Lemmon\I18n;

class I18n
{
    private $_dir = BASE_DIR . '/i18n';
    private $_locale = 'en_US';
    private $_cache = [];
    private $_domains = [];
    private $_currentDomain;
    private $_plural;


    function setLocale($locale)
    {
        $this->_locale = $locale;
        return $this;
    }


    function _print($str, array $args)
    {
        if ($args) {
            if (array_key_exists(0, $args)) {
                return vsprintf($str, $args);
            } else {
                foreach ($args as $key => $val) {
                    $str = str_replace(':' . $key, $val, $str);
                }
            }
        }
        return $str;
    }


    function t($str, $args = [])
    {
        if ($args and !is_array($args)) {
            $args = func_get_args();
            array_shift($args);
        }
        if ($res = @$this->_domains[$this->_currentDomain][$str]) {
            return $this->_print($res, $args);
        }
        return $this->_print($str, $args);
    }


    function tn($str, $n, $args = [])
    {
        if (isset($this->_currentDomain) and $res = @$this->_domains[$this->_currentDomain][$str]) {
            $i = $this->getPlural($n);
        } else {
            $res = $str;
            $i = $this->getDeaultPlural($n);
        }
        $res = explode('|', $res);
        $res = str_replace(':n', $n, $res[$i]);
        return $res;
    }


    function getPlural($n)
    {
        return (is_callable($this->_plural) and $func = $this->_plural) ? $func($n) : $this->getDeaultPlural($n);
    }


    function getDeaultPlural($n)
    {
        return ($n == 0 or $n > 1) ? 1 : 0;
    }


    function getCountries()
    {
        return @$this->_cache['countries'] ?: $this->_cache['countries'] = $this->_sort(array_diff_key($this->_include('countries')['countries'], $this->getTerritories()));
    }


    function getTerritories()
    {
        return @$this->_cache['territories'] ?: $this->_cache['territories'] = include BASE_DIR . "/i18n/territories.php";
    }


    function getTimezones()
    {
        return array_combine(timezone_identifiers_list(), timezone_identifiers_list());
    }


    private function _getCollator()
    {
        return @$this->_cache['collator'][$this->_locale] ?: ($this->_cache['collator'][$this->_locale] = new \Collator($this->_locale));
    }


    private function _sort($array)
    {
        $this->_getCollator()->asort($array);
        return $array;
    }


    private function _include($name)
    {
        return include BASE_DIR . "/i18n/{$this->_locale}/{$name}.php";
    }
}
