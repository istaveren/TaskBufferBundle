<?php
namespace Bundle\TaskBufferBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\TaskBufferBundle\Entity\TaskBuffer;

class TaskBufferController extends Controller
{
	
    public function tasksAction( $offset = 0, $limit = 10 )
    {
    	$em = $this->get('doctrine.orm.entity_manager');

    	$taskBuffer = $this->get( 'task_buffer.task_buffer' );
    	$taskBuffer->queue( '\Bundle\TaskBufferBundle\Tests\Model\ObjectX::someMethodOk' );
    	
    	$objectX = new \Bundle\TaskBufferBundle\Tests\Model\ObjectX();
    	$taskBuffer->queue( array( $objectX, 'someMethodOk2' ) );
    	
    	
    	$offset = 0;
    	$limit = 10;
    	
        $query = $em->createQuery( "SELECT t, tg FROM \Bundle\TaskBufferBundle\Entity\TaskGroup tg JOIN tg.tasks t WHERE tg.failuresLimit > t.failuresCount AND ( ( tg.startTime < CURRENT_TIME() OR tg.startTime is NULL ) AND ( tg.endTime > CURRENT_TIME() OR tg.endTime is NULL ) ) ORDER BY tg.priority DESC, t.createdAt ASC" )
        	->setFirstResult( $offset )
    		->setMaxResults( $limit );
		$taskGroups = $query->getResult();

		var_dump( $taskGroups[0]->getTasks() );
////////    	    	
    	
//
//    	
//    	$task = new \Bundle\TaskBufferBundle\Entity\Task();
//    	
//    	$em->persist( $TaskBuffer->queue( array( $task, 'a' ), 'trzecia' ) );
//    	$em->persist( $TaskBuffer->queue( array( $task, 'b' ), 'trzecia' ) );
//    	$em->persist( $TaskBuffer->queue( array( $task, 'c' ), 'czwarta' ) );
//    	$em->persist( $TaskBuffer->queue( array( $task, 'd' ), 'piąta' ) );
//    	$group = $TaskBuffer->queue( array( $task, 'e' ) );
//    	
//    	$em->persist( $group );
//		$em->flush();
////////		
//    	$limit = 3;
//
//    	$query = $em->createQuery( "SELECT t, tg FROM \Bundle\TaskBufferBundle\Entity\TaskGroup tg JOIN tg.tasks t WHERE t.executedAt is NULL AND tg.failuresLimit > t.failuresCount AND ( ( tg.startTime < CURRENT_TIME() OR tg.startTime is NULL ) AND ( tg.endTime > CURRENT_TIME() OR tg.endTime is NULL ) ) ORDER BY tg.priority DESC, t.createdAt ASC" )
//    	->setMaxResults( $limit );
//		$taskGroup = $query->getResult();
//		var_dump( $taskGroup[0]->getTasks() );
    	
		return $this->render( 'TaskBufferBundle:TaskBuffer:tasks.php.html', array( 'taskGroups' => $taskGroups ) );
    }
    
    public function taskDeleteAction()
    {
    	
    }
    
}