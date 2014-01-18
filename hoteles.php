<?php
class Hoteles
{
    private $dp;
	
	function __construct()
    {
		$this->dp = new Storage_PDO();
    }
	
	/**
      * @param string $term
      * @return string
    */
	protected function get($term = string)
   	{
		return $this->dp->get($term);
	}
}
