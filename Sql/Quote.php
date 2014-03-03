<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Sql;

/**
 * SQL quoting engine.
 */
class Quote
{


    static function field($field)
    {
        if (is_array($field)) {
            foreach ($field as $i => $_field) {
                $field[$i] = self::field($_field);
            }
            return $field;
        } else {
            $field = explode('.', trim($field, '.'));
            foreach ($field as $i => $_field) {
                if ($_field != '*') {
                    $field[$i] = '`' . self::_field($_field) . '`';
                }
            }
            return join('.', $field);
        }
    }


    static function value($value)
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_array($value)) {
            return '\'' . join('\', \'', self::_value($value)) . '\'';
        } elseif (is_int($value) or is_double($value)) {
            return $value;
            // setlocale() issue
            // return str_replace([localeconv()['decimal_point'], localeconv()['thousands_sep']], ['.', ''], $value);
        } else {
            return sprintf('\'%s\'', self::_value($value));
        }
    }


    static function _field($field)
    {
        return str_replace('`', '\\`', $field);
    }


    static function _value($value)
    {
        return str_replace(['\\', '\''], ['\\\\', '\\\''], $value);
    }
}