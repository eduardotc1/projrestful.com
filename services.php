<?php
class Services
{
    private $dp;
	
	function __construct()
    {
		$this->dp = new Storage_PDOServices();
    }
	
	/**
	  * @param string $key
      * @param string $fromID
      * @param string $toID
      * @param string $pax
      * @param string $code
      * @param string $fromDestID
      * @param string $toDestID
      * @return string
    */
	protected function get($key = string, $fromID = string, 
		$toID = string, $pax = string, 
		$code = string, $fromDestID = string, 
		$toDestID = string)
   	{
		return $this->dp->get($key, $fromID, 
			$toID, $pax, 
			$code, $fromDestID, 
			$toDestID
		);
	}
}

//http://www.api.cancunreservation.net/services?key=b04b77ac5851224ac844547bc6e61915&fromID=10&toID=15&pax=1&code-cap&fromDestID=14&toDestID=15
