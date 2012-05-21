<?php
/**
* 
*/
class Lemmon_Exception extends Exception
{
	protected $fatal = true;
	
	public function isFatal()
	{
		return $this->fatal;
	}	
}
