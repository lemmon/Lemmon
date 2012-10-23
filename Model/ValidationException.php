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
 * Model.
 */
class ValidationException extends \Exception
{
	protected $_fields = [];


	function __construct($message, array $fields=[])
	{
		$this->fields = $fields;
		
		parent::__construct($message);
	}


	function getFields()
	{
		return $this->fields;
	}
}