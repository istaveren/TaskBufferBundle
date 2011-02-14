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
    	$taskGroup1->setPriority( 100 );
    	$taskGroup1->setFailuresLimit( 3 );
    	
    	$task1 = new TaskCallable();
    	$task1->setTaskId( 1 );
    	$task1->setTaskGroup( $taskGroup1 );
    	$task1->setCallable( 'a' );
    	$task1->setStatus( Task::STATUS_AWAITING );
    	$task1->setFailuresCount( 0 );
    	$task1->setCreatedAt( '2011-01-17 22:00:15' );

    	$task2 = new TaskCallable();
    	$task2->setTaskId( 2 );
    	$task2->setTaskGroup( $taskGroup1 );
    	$task2->setCallable( 'invalidCallbackX' );
    	$task1->setStatus( Task::STATUS_AWAITING );
    	$task2->setFailuresCount( 0 );
    	$task2->setCreatedAt( '2011-02-19 12:10:35' );

    	$task3 = new TaskCallable();
    	$task3->setTaskId( 3 );
    	$task3->setTaskGroup( $taskGroup1 );
    	$task3->setCallable( 'zooom' );
    	$task1->setStatus( Task::STATUS_AWAITING );
    	$task3->setFailuresCount( 0 );
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
        $this->assertEquals( 1, $task->getFailuresCount() );
    }

    
    
   
}