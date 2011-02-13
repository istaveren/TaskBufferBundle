<?php

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\Task;
use Bundle\TaskBufferBundle\Entity\TaskGroup;
use Bundle\TaskBufferBundle\Entity\TaskBufferException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;

class TaskBuffer
{
	private $em;
	
	private $groups;
	
	private $currentGroupIdentifier;
	
	private $output;
	
	private $limit = 10;
	
	private $priority = 100;
	
	private $failuresLimit = 3;

	public function __construct( $em )
	{
		$this->em = $em;
		$this->groups = new ArrayCollection();
	} 
	
	public function setOutput( OutputInterface $output )
	{
		$this->output = $output;
	}
	
	public function setLimit( $limit )
	{
	    $this->limit = $limit;    
	}
	
	public function getLimit()
	{
	    return $this->limit;
	}

	public function setPriority( $priority )
	{
	    $this->priority = $priority;
	}
	
	public function getPriority()
	{
	    return $this->priority;
	}

	public function setFailuresLimit( $failuresLimit )
	{
	    $this->failuresLimit = $failuresLimit;    
	}
	
	public function getFailuresLimit()
	{
	    return $this->failuresLimit;
	}
		
	public function queue( $callable, $groupIdentifier = 'standard' )
	{
	    $this->em->persist( $this->initialize( $callable, $groupIdentifier ) );
        $this->em->flush();
	}
	
	public function initialize( $callable, $groupIdentifier = 'standard' )
	{
		$this->currentGroupIdentifier = $groupIdentifier;
		$this->setGroupByCurrentIdentifier();

		$group = ( is_array( $callable ) ) ? $this->initializeTaskForObject( $callable ) : $this->initializeTaskForMethod( $callable );

		return $group;
	}
	
	public function setGroupByCurrentIdentifier()
	{
		if( !$this->groups->containsKey( $this->currentGroupIdentifier ) )
		{
			$groups = $this->em->createQuery( "SELECT tg FROM Bundle\TaskBufferBundle\Entity\TaskGroup tg WHERE tg.identifier = '$this->currentGroupIdentifier'" )->getResult();	
			
			$group = ( isset( $groups[0] ) && $groups[0] instanceof TaskGroup ) ? $groups[0] : $this->initializeGroup();
			$this->groups->set( $this->currentGroupIdentifier, $group );
		}
	}
	
	private function checkArrayForCallableWithObject( $callableWithObject )
	{
		if( !isset( $callableWithObject[0] ) || !isset( $callableWithObject[1] ) )
		{
			throw new TaskBufferException( "There is no callable!" );
		}
	}
	
	public function initializeTaskForObject( $callableWithObject )
	{
		$this->checkArrayForCallableWithObject( $callableWithObject );
		$object = $callableWithObject[0];
		$callable = $callableWithObject[1];
		
		//TODO: initialization form IoC Container!
		$task = new TaskCallableOnObject();
		$task = $this->initializeTask( $task );
		$task->setCallable( $callable );
		$task->setObject( $object );		

		return $this->groups->get( $this->currentGroupIdentifier );
	}
	 
	public function initializeTaskForMethod( $callable )
	{
		//TODO: initialization form IoC Container!	    
	    $task = new TaskCallable();
		$task = $this->initializeTask( $task );
		$task->setCallable( $callable );
		return $this->groups->get( $this->currentGroupIdentifier );
	}
	
	private function initializeTask( $task )
	{
		$task->setFailuresCount( 0 );
		$task->setStatus( Task::STATUS_AWAITING );
		$this->groups->get( $this->currentGroupIdentifier )->addTask( $task );
		$task->setTaskGroup( $this->groups->get( $this->currentGroupIdentifier ) );
		
	    return $task; 
	}
	
	public function pull( $ignoreFailures )
	{
        // suspend auto-commit
    	$this->em->getConnection()->beginTransaction(); 
    	try {
	    
    		$codeSuccess = Task::STATUS_SUCCESS;
            $query = $this->em->createQuery( "SELECT t, tg FROM \Bundle\TaskBufferBundle\Entity\TaskGroup tg JOIN tg.tasks t WHERE tg.failuresLimit > t.failuresCount AND ( ( tg.startTime < CURRENT_TIME() OR tg.startTime is NULL ) AND ( tg.endTime > CURRENT_TIME() OR tg.endTime is NULL ) ) AND ( t.status IS NULL OR t.status != {$codeSuccess} ) ORDER BY tg.priority DESC, t.createdAt ASC" )
        		->setMaxResults( $this->limit );
        		
            $query->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
    		$taskGroups = $query->getResult();

    		foreach( $taskGroups as $taskGroup )
    		{
    			$taskGroup->setOutput( $this->output );
    			$taskGroup->execute( $this->em, $ignoreFailures );	
    		}
		
            $this->em->getConnection()->commit();    		
    	} catch (Exception $e) {
            $this->em->getConnection()->rollback();
            $this->em->close();
            throw $e;
        }    		    
    	
	}
	
	private function initializeGroup()
	{
		$group = new TaskGroup();
		$group->setIdentifier( $this->currentGroupIdentifier );
		$group->setPriority( $this->getPriority() );
		$group->setFailuresLimit( $this->getFailuresLimit() );
		return $group;
	}

}