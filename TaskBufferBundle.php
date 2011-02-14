<?php

namespace Smentek\TaskBufferBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TaskBufferBundle extends Bundle
{
	public function getNamespace()
	{
		return __NAMESPACE__;
	}
	
	public function getPath()
	{
		return dirname ( __FILE__ );
	}
}