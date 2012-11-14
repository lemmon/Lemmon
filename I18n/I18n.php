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


	function getLocales()
	{
		if (array_key_exists('locales', self::$_cache))
			return self::$_cache['locales'];
		else
			return self::$_cache['locales'] = include __DIR__ . '/data/locales.php';
	}
}