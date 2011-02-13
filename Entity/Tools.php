<?php
namespace Bundle\TaskBufferBundle\Entity;

class Tools
{
	public static function timeInMicroseconds() 
	{
        list($usec, $sec) = explode(" ", microtime());
        
        return ((float)$usec + (float)$sec);
	}
}