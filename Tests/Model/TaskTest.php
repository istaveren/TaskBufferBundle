<?php

namespace Smentek\TaskBuffreBundle\Tests\Model;

use Smentek\TaskBufferBundle\Entity\Tools;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskCallable;
use Smentek\TaskBufferBundle\Entity\TaskGroup;


//static method callback: EmailSender::send
//object method callback: array( $this, 'statusUpdated' )

class TaskTest extends WebTestCase
{
	public function invalidCallbackTasks()
	{
    	$taskGroup1 = new taskGroup();
    	$taskGroup1->setTaskGroupId( 1 );
    	$taskGroup1->setIdentifier( 'standard' );
    	
    	$task1 = new TaskCallable();
    	$task1->setTaskId( 1 );
    	$task1->setTaskGroup( $taskGroup1 );
    	$task1->setCallable( 'a' );
    	$task1->setStatus( Task::STATUS_AWAITING );
    	$task1->setPriority( 100 );
    	$task1->setFailuresLimit( 3 );
    	$task1->setCreatedAt( '2011-01-17 22:00:15' );

    	$task2 = new TaskCallable();
    	$task2->setTaskId( 2 );
    	$task2->setTaskGroup( $taskGroup1 );
    	$task2->setCallable( 'invalidCallbackX' );
    	$task2->setStatus( Task::STATUS_AWAITING );
    	$task2->setPriority( 100 );
    	$task2->setFailuresLimit( 3 );
    	$task2->setCreatedAt( '2011-02-19 12:10:35' );

    	$task3 = new TaskCallable();
    	$task3->setTaskId( 3 );
    	$task3->setTaskGroup( $taskGroup1 );
    	$task3->setCallable( 'zooom' );
    	$task3->setStatus( Task::STATUS_AWAITING );
    	$task3->setPriority( 100 );
    	$task3->setFailuresLimit( 3 );
    	$task3->setCreatedAt( '2010-12-10 02:11:34' );
    	
    	return array( 
    		array( 
    			$task1,  
    			$task2,
    			$task3,
    		) 
    	);
	}

	/**
	 * 
	 * @dataProvider invalidCallbackTasks
	 */
    public function testExecuteInvalidCallbackTask( $task )
    {
    	$task->execute();
    	
        $this->assertTrue( $task->getDuration() != null );
        $this->assertTrue( $task->getExecutedAt() != null );
        $this->assertEquals( Task::STATUS_INVALID_CALLABACK , $task->getStatus() );
        $this->assertEquals( 1, $task->getFailures() );
    }

    
    
   
}