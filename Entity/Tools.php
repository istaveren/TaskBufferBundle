<?php
namespace Bundle\TaskBufferBundle\Entity;

class Tools
{
	public static function timeInMicroseconds( $microime ) 
	{
		$timeparts = explode( " ", $microime );
		return bcadd( ( $timeparts[0] * 1000000 ), bcmul( $timeparts[1], 1000000 ) );
	}
			
		
	
}