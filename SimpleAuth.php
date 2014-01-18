<?php

use Luracast\Restler\iAuthenticate;

class SimpleAuth implements iAuthenticate
{
	private $dp;
	private $key;
	private $source;
	private $target;
	private $replace;
	private $checkmd5;
	
	function __construct()
    {
		$this->dp = new Storage_PDO();
    }
	
    function __isAllowed()
    {	
		$this->key = $_GET['key'];
		if (isset($this->key) && strlen($this->key) == 32 ) 
		{
			$this->source = array("a", "e", "i", "o", "u");
			$this->target = array("97", "101", "105", "111", "117");
			$this->replace = str_replace($this->source, $this->target, $this->key);
			$this->checkmd5 = md5($this->key.$this->replace);
			return $this->dp->key($this->key,$this->checkmd5) ? TRUE : FALSE;			
		}
	}
}
