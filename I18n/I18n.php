<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\I18n;

/**
 * Model.
 */
class I18n
{
    static $_cache = [];

    private $_localeId;
    private $_dictonary = [];


    function __construct($locale_id = 'en')
    {
        $this->setLocale($locale_id);
    }


    static function getLocales()
    {
        return include __DIR__ . '/data/locales.php';
    }


    static function getCountries()
    {
        return include __DIR__ . '/data/countries.php';
    }


    function setLocale($locale_id)
    {
        $this->_localeId = $locale_id;
    }


    function load($file)
    {
        if (file_exists($file)) {
            $data = include $file;
            if (is_array($data)) {
                $this->_dictonary = array_merge($this->_dictonary, $data);
            }
        }
    }


    function t($phrase)
    {
        $args = func_get_args();
        array_shift($args);
        $phrase = array_key_exists($phrase, $this->_dictonary) ? $this->_dictonary[$phrase] : $phrase;
        if ($args) {
            $phrase = vsprintf($phrase, $args);
        }
        return $phrase;
    }
}