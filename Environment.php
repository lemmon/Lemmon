<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon;

/**
 * Handles environments.
 * 
 * @author Jakub Pelák <jpelak@gmail.com>
 */
class Environment
{
    const TYPE_DEVELOPMENT = 'development';
    const TYPE_PRODUCTION = 'production';

    static private $_development = null; // depreciated


    protected function __init() {}


    /**
     * Constructor.
     */
    function __construct()
    {
        // depreciated
        // defaults
        if (is_null(self::$_development)) {
            if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') {
                self::setDev(true);
            }
        }
        // /depreciated
        
        // init class
        $this->__init();
    }


    /**
     * Set development environment.
     */
    static function setDev($is = true)
    {
        // depreciated
        self::$_development = (bool)$is;
    }


    /**
     * Test for development environment.
     * @return bool
     */
    static function isDev()
    {
        // depreciated
        return (bool)self::$_development;
    }


    /**
     * Test for production environment.
     * @return bool
     */
    static function isProduction()
    {
        // depreciated
        return !(bool)self::$_development;
    }
}
