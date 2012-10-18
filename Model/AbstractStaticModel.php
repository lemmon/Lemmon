<?php

/*
 * This file is part of the Lemmon Framework (http://framework.lemmonjuice.com).
 *
 * Copyright (c) 2007 Jakub Pelák (http://jakubpelak.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lemmon\Model;

/**
 * Static model.
 */
abstract class AbstractStaticModel /*implements \IteratorAggregate*/
{
	static protected $data = [];
	
	protected $name = 'name';
	
	private $_id;


	function __construct($id = null)
	{
		// id
		if (isset($id))
		{
			$this->_id = $id;
		}
	}
}