<?php
namespace Bundle\TaskBufferBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bundle\TaskBufferBundle\Entity\TaskBuffer;
use Bundle\TaskBufferBundle\Entity\Task;

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
    	
    	//TODO: count all task by statuses
    	
        $query = $em->createQuery( "SELECT tg FROM \Bundle\TaskBufferBundle\Entity\TaskGroup tg
        	WHERE tg.isActive = true ORDER BY tg.priority ASC" )
        	->setFirstResult( $offset )
    		->setMaxResults( $limit );
		$taskGroups = $query->getResult();

		$statusSuccess = Task::STATUS_SUCCESS;
	    $statusAwaiting = Task::STATUS_AWAITING;
        $statusInvalidCallback = Task::STATUS_INVALID_CALLABACK;
        $statusRuntimeException = Task::STATUS_RUNTIME_EXCEPTION;
		
		$statusSuccesQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Bundle\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusSuccess}" ); 
		$statusSuccessQuantity = $statusSuccesQuantityQuery->getSingleScalarResult();

		$statusAwaitingQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Bundle\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusAwaiting}" ); 
		$statusAwaitingQuantity = $statusAwaitingQuantityQuery->getSingleScalarResult();

		$statusInvalidCallbackQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Bundle\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusInvalidCallback}" ); 
		$statusInvalidCallbackQuantity = $statusInvalidCallbackQuantityQuery->getSingleScalarResult();
		
		$statusRuntimeExceptionQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Bundle\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusRuntimeException}" ); 
		$statusRuntimeExceptionQuantity = $statusRuntimeExceptionQuantityQuery->getSingleScalarResult();
		
		$viewResult = array(
		    'statusSuccessQuantity' => $statusSuccessQuantity,
		    'statusAwaitingQuantity' => $statusAwaitingQuantity,
		    'statusInvalidCallbackQuantity' => $statusInvalidCallbackQuantity,
		    'statusRuntimeExceptionQuantity' => $statusRuntimeExceptionQuantity,
		    'taskGroups' => $taskGroups,
		);
		
		return $this->render( 'TaskBufferBundle:TaskBuffer:tasks.php.html', $viewResult );
    }
    
    public function taskDeleteAction()
    {
    	
    }
    
}