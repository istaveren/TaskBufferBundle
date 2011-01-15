<?php
namespace Bundle\TaskBufferBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\TaskBufferBundle\Entity\TaskManager;



class TaskBufferController extends Controller
{
	
	public static function x()
	{
			echo "!BUM!";
	}
	
    public function tasksAction()
    {
    	$em = $this->get('doctrine.orm.entity_manager');
////////    	    	
//    	$taskManager = $this->get( 'task_buffer.task_manager' );
//
//    	
//    	$task = new \Bundle\TaskBufferBundle\Entity\Task();
//    	
//    	$em->persist( $taskManager->queue( array( $task, 'a' ), 'trzecia' ) );
//    	$em->persist( $taskManager->queue( array( $task, 'b' ), 'trzecia' ) );
//    	$em->persist( $taskManager->queue( array( $task, 'c' ), 'czwarta' ) );
//    	$em->persist( $taskManager->queue( array( $task, 'd' ), 'piąta' ) );
//    	$group = $taskManager->queue( array( $task, 'e' ) );
//    	
//    	$em->persist( $group );
//		$em->flush();
////////		
    	$limit = 3;

    	$query = $em->createQuery( "SELECT t, tg FROM \Bundle\TaskBufferBundle\Entity\TaskGroup tg JOIN tg.tasks t WHERE t.executedAt is NULL AND tg.failuresLimit > t.failuresCount AND ( ( tg.startTime < CURRENT_TIME() OR tg.startTime is NULL ) AND ( tg.endTime > CURRENT_TIME() OR tg.endTime is NULL ) ) ORDER BY tg.priority DESC, t.createdAt ASC" )
    	->setMaxResults( $limit );
		$taskGroup = $query->getResult();
		var_dump( $taskGroup[0]->getTasks() );
    	
    	
		return $this->render( 'TaskBufferBundle:TaskBuffer:tasks.php' );
    }
    
    public function taskDeleteAction()
    {
    	
    }
    
}