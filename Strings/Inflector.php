<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Strings;

/**
 * @author Jakub Pelák <jpelak@gmail.com>
 */
class Inflector
{


    static function capitalize($str, $encoding = 'UTF-8')
    {
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_strtolower(mb_substr($str, 1, NULL, $encoding), $encoding);
        return $str;
    }


    static function center($str, $width, $padstr = ' ', $encoding = 'UTF-8')
    {
        $len = mb_strlen($str, $encoding);
        if ($len > $width) {
            $str = mb_substr($str, 0, $len, $encoding);
        } elseif ($len < $width) {
            #$pad = ($width - $len) / 2;
            $str = str_pad($str, $width, $padstr, STR_PAD_BOTH);
        }
        return $str;
    }


    static function delete($str, $other_str, $encoding = 'UTF-8')
    {
        return self::replace($str, $other_str, '');
    }


    static function downcase($str, $encoding = 'UTF-8')
    {
        $str = mb_strtolower($str, $encoding);
        return $str;
    }


    static function eachChar($str)
    {
        return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
    }


    static function eachWord($str)
    {
        return preg_split('/ /u', $str, -1, PREG_SPLIT_NO_EMPTY);
    }


    static function index($str, $substr, $offset = 0, $encoding = 'UTF-8')
    {
        return mb_strpos($str, $substr, $offset, $encoding);
    }


    static function length($str, $encoding = 'UTF-8')
    {
        return mb_strlen($str, $encoding);
    }


    static function replace($str, $match, $other_str, $encoding = 'UTF-8')
    {
        $str = preg_replace("/{$match}/u", $other_str, $str);
        return $str;
    }


    static function titlecase($str, $encoding = 'UTF-8')
    {
        $str = mb_convert_case($str, MB_CASE_TITLE, $encoding);
        return $str;
    }


    static function upcase($str, $encoding = 'UTF-8')
    {
        $str = mb_strtoupper($str, $encoding);
        return $str;
    }


    //


    static function asciize($str, $encoding = 'UTF-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $encoding);
        $str = preg_replace('/&(\w+)(acute|caron|circ|cedil|grave|lig|nof|ring|tilde|uml);/', '\1', $str);
        $str = html_entity_decode($str, ENT_NOQUOTES);
        $str = iconv($encoding, 'ASCII//TRANSLIT', $str);
        return $str;
    }


    static function camelize($str, $downcase_first = FALSE, $encoding = 'UTF-8')
    {
        $str = str_replace('_', ' ', $str);
        $str = str_replace('/', '_', $str);
        $str = preg_replace('/[^\w]/u', ' ', $str);
        $str = mb_convert_case($str, MB_CASE_TITLE, $encoding);
        $str = str_replace([' ', '_'], ['', '\\'], $str);
        if ($downcase_first) {
            $str = mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, NULL, $encoding);
        }
        return $str;
    }


    static function dasherize($str, $encoding = 'UTF-8')
    {
        $str = strtr($str, '_', '-');
        return $str;
    }


    static function humanize($str, $titlecase = FALSE, $encoding = 'UTF-8')
    {
        $str = preg_replace('/_id$/', '', $str);
        $str = strtr($str, '_', ' ');
        $str = preg_replace('/[^\w]+/u', ' ', $str);
        $str = trim($str);
        if ($titlecase) {
            $str = self::titlecase($str, $encoding);
        } else {
            $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_substr($str, 1, NULL, $encoding);
        }
        return $str;
    }


    static function slug($str, $sep = '-', $keep_case = FALSE, $encoding = 'UTF-8')
    {
        $str = self::asciize($str);
        $str = strtr($str, '_', ' ');
        $str = preg_replace('/[^\w]+/', $sep, $str);
        $str = trim($str, $sep);
        if (!$keep_case) {
            $str = strtolower($str);
        }
        return $str;
    }


    static function underscore($str, $encoding = 'UTF-8')
    {
        $str = preg_replace_callback('/((\p{Lu}+)(\p{Lu}\p{Ll})|(\p{Lu}+))/Uu', function($m) use ($encoding) {
            return '_' . mb_strtolower(isset($m[4]) ? $m[4] : "$m[2]_$m[3]", $encoding);
        }, $str);
        $str = str_replace(['\\_', '\\'], '/', ltrim($str, '_'));
        return $str;
    }
}