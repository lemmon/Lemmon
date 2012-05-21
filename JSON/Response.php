<?php
/**
 * Handles JSON responses.
 *
 * @copyright  Copyright (c) 2007-2010 Jakub Pelák
 * @author     Jakub Pelák <jpelak@gmail.com>
 * @link       http://www.lemmonjuice.com
 * @package    lemmon
 */
class Lemmon_JSON_Response
{
	private $_data = array();
	private $_dataToCollect = array();
	private $_responseType = 'lemmon';
	
	function __construct($response_data=null, $type='plain')
	{
		if ($response_data!==null)
		{
			if ($type=='plain')
			{
				array_push($this->_data, $response_data);
				$this->_responseType = 'plain';
			}
			elseif ($type=='data')
			{
				$this->_data = $response_data;
				$this->_responseType = 'data';
			}
		}
	}
	
	function __toString()
	{
		$data = $this->_data;
		if ($data_to_collect=$this->_dataToCollect)
		{
			$controller = Lemmon_Framework::getInstance();
			foreach ($data_to_collect as $key)
			{
				$data[$key] = $controller->data[$key];
			}
		}
		
		if ($this->_responseType=='plain')
		{
			$res = reset($data);
		}
		elseif ($this->_responseType=='data')
		{
			$res = $data;
		}
		else
		{
			die('[TODO] Response');
		}
		
		return json_encode($res);
	}
	
	function type($type=null)
	{
		if ($type!==null)
		{
			$this->_responseType = $type;
			return $this;
		}
		else
		{
			return $this->_responseType;
		}
	}
	
	function put($key, $val=null)
	{
		if ($val!==null)
		{
			$this->_data[$key] = $val;
		}
		else
		{
			$this->_dataToCollect[$key] = $key;
		}
		return $this;
	}
}
