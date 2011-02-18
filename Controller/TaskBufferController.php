<?php

namespace Smentek\TaskBufferBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Smentek\TaskBufferBundle\Entity\TaskBuffer;
use Smentek\TaskBufferBundle\Entity\Task;
use Zend\Paginator\Paginator;
use ZendPaginatorAdapter\DoctrineORMAdapter;


class TaskBufferController extends Controller
{
	
    public function tasksAction( $offset = 0, $limit = 10 )
    {
    	$em = $this->get('doctrine.orm.entity_manager');

    	$taskBuffer = $this->get( 'task_buffer.task_buffer' );
    	$taskBuffer->queue( '\Smentek\TaskBufferBundle\Tests\Model\ObjectX::someMethodOk', 'group_11' );
    	
    	$objectX = new \Smentek\TaskBufferBundle\Tests\Model\ObjectX();
    	$taskBuffer->queue( array( $objectX, 'someMethodOk2' ), 'group_13' );
    	
    	$offset = 0;
    	$limit = 10;
    	
    	//TODO: count all task by statuses
    	
        $queryGroupsOnPage = $em->createQuery( "SELECT tg FROM \Smentek\TaskBufferBundle\Entity\TaskGroup tg WHERE tg.isActive = true ORDER BY tg.priority ASC" )
        	->setFirstResult( $offset )
    		->setMaxResults( $limit );
		$taskGroups = $queryGroupsOnPage->getResult();

		$groups = new Paginator( new DoctrineORMAdapter( $queryGroupsOnPage ) );
		$groups->setCurrentPageNumber(1);
        $groups->setItemCountPerPage(10);
        $groups->setPageRange(3);
        $pagerUrl = $this->generateUrl('fos_user_user_list');
		
		
		
		$queryAllGroupsQuantity = $em->createQuery( "SELECT COUNT( tg ) FROM \Smentek\TaskBufferBundle\Entity\TaskGroup tg WHERE tg.isActive = true ORDER BY tg.priority ASC" );
		$quantityOfTaskGroups = $queryAllGroupsQuantity->getSingleScalarResult();

		$statusSuccess = Task::STATUS_SUCCESS;
	    $statusAwaiting = Task::STATUS_AWAITING;
        $statusInvalidCallback = Task::STATUS_INVALID_CALLABACK;
        $statusRuntimeException = Task::STATUS_RUNTIME_EXCEPTION;
		
		$statusSuccesQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusSuccess}" ); 
		$statusSuccessQuantity = $statusSuccesQuantityQuery->getSingleScalarResult();

		$statusAwaitingQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusAwaiting}" ); 
		$statusAwaitingQuantity = $statusAwaitingQuantityQuery->getSingleScalarResult();

		$statusInvalidCallbackQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusInvalidCallback}" ); 
		$statusInvalidCallbackQuantity = $statusInvalidCallbackQuantityQuery->getSingleScalarResult();
		
		$statusRuntimeExceptionQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusRuntimeException}" ); 
		$statusRuntimeExceptionQuantity = $statusRuntimeExceptionQuantityQuery->getSingleScalarResult();
		
		
		
		$viewResult = array(
		    'statusSuccessQuantity' => $statusSuccessQuantity,
		    'statusAwaitingQuantity' => $statusAwaitingQuantity,
		    'statusInvalidCallbackQuantity' => $statusInvalidCallbackQuantity,
		    'statusRuntimeExceptionQuantity' => $statusRuntimeExceptionQuantity,
		    'taskGroups' => $taskGroups,
            'quantity_of_task_groups' => $quantityOfTaskGroups,
		);

		return $this->render('TaskBufferBundle:TaskBuffer:tasks.html.twig', $viewResult);
    }
    
    public function taskDeleteAction()
    {
    	
    }
    
}