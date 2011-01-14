<?php
namespace Bundle\TaskBufferBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TaskBufferController extends Controller
{
    public function TasksAction()
    {
		return $this->render( 'TaskBufferBundle:TaskBuffer:tasks.php' );
    }
}