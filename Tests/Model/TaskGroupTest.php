<?php

namespace Smentek\TaskBuffreBundle\Tests\Model;

use Smentek\TaskBufferBundle\Entity\Tools;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskCallableOnObject;
use Smentek\TaskBufferBundle\Tests\Model\ObjectX;
use Smentek\TaskBufferBundle\Entity\TaskGroup;

class TaskGroupTest extends WebTestCase
{

	public function runtimeExceptionTasks()
	{
        $objectX = new ObjectX();
	    
	    
	    
    	$taskGroup1 = new taskGroup();
    	$taskGroup1->setTaskGroupId( 1 );
    	$taskGroup1->setIdentifier( 'standard' );
    	$taskGroup1->setPriority( 100 );
    	$taskGroup1->setFailuresLimit( 3 );
    	
    	$task1 = new TaskCallableOnObject();
    	$task1->setTaskId( 1 );
    	$task1->setTaskGroup( $taskGroup1 );
    	$task1->setCallable( 'someMethod' );
    	$task1->setStatus( Task::STATUS_AWAITING );
    	$task1->setObject( $objectX );
    	$task1->setFailuresCount( 0 );
    	$task1->setCreatedAt( '2011-01-17 22:00:15' );

    	$taskGroup1->setTasks( array( $task1 ) );
//    	$task2 = new Task();
//    	$task2->setTaskId( 2 );
//    	$task2->setTaskGroup( $taskGroup1 );
//    	$task2->setCallable( 'someMethod2' );
//    	$task1->setObject( $objectX );
//    	$task2->setFailuresCount( 0 );
//    	$task2->setCreatedAt( '2011-02-19 12:10:35' );
//
//    	$task3 = new Task();
//    	$task3->setTaskId( 3 );
//    	$task3->setTaskGroup( $taskGroup1 );
//    	$task3->setCallable( 'zooom2' );
//    	$task1->setObject( $objectX );
//    	$task3->setFailuresCount( 0 );
//    	$task3->setCreatedAt( '2010-12-10 02:11:34' );
    	
    	return array( 
    		array( 
    			$taskGroup1
//    			$task2,
//    			$task3,
    		) 
    	);
	}
	
    
    
	/**
	 * 
	 * @dataProvider runtimeExceptionTasks
	 */
    public function testExecuteRuntimeExceptionTask( $taskGroup1 )
    {
        
        //TODO: przeniesc poziom wyzej do task manager
    	$taskGroup1->execute();
    	
    	$tasks = $taskGroup1->getTasks();
    	$task = $tasks[0];
//        $this->assertTrue( $task->getDuration() != null );
//        $this->assertTrue( $task->getExecutedAt() != null );
        $this->assertEquals( Task::STATUS_RUNTIME_EXCEPTION , $task->getStatus() );
//        $this->assertEquals( 1, $task->getFailuresCount() );
    }	
    
}