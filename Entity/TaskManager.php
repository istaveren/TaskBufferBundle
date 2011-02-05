<?php

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\Task;
use Bundle\TaskBufferBundle\Entity\TaskGroup;
use Bundle\TaskBufferBundle\Entity\TaskBufferException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;

class TaskManager
{
	private $em;
	
	private $groups;
	
	private $currentGroupIdentifier;
	
	private $output;

	public function __construct( $em )
	{
		$this->em = $em;
		$this->groups = new ArrayCollection();
	} 
	
	public function setOutput( OutputInterface $output )
	{
		$this->output = $output;
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

		//TODO: zapis grupy (a wraz nia umieszczonego w niej tasku)
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
	
	public function initializeTaskForObject( $callableWithObject )
	{
		if( !isset( $callableWithObject[0] ) || !isset( $callableWithObject[1] ) )
		{
			throw new TaskBufferException( "Callable improperly set!" );
		}
		
		$object = $callableWithObject[0];
		$callable = $callableWithObject[1];
		$task = $this->initializeTask( $callable );
		
		$task->setObject( $object );		

		return $this->groups->get( $this->currentGroupIdentifier );
	}
	 
	public function initializeTaskForMethod( $callable )
	{
		$task = $this->initializeTask( $callable );
		
		return $this->groups->get( $this->currentGroupIdentifier );
	}
	
	private function initializeTask( $callable )
	{
		$task = new Task();
		$task->setCallable( $callable );
		$task->setFailuresCount( 0 );
		$task->setStatus( Task::STATUS_AWAITING );
		$this->groups->get( $this->currentGroupIdentifier )->addTask( $task );
		$task->setTaskGroup( $this->groups->get( $this->currentGroupIdentifier ) );
		
	    return $task; 
	}
	
	public function pull( $limit, $ignoreFailures )
	{
		$codeSuccess = Task::STATUS_SUCCESS;
        $query = $this->em->createQuery( "SELECT t, tg FROM \Bundle\TaskBufferBundle\Entity\TaskGroup tg JOIN tg.tasks t WHERE tg.failuresLimit > t.failuresCount AND ( ( tg.startTime < CURRENT_TIME() OR tg.startTime is NULL ) AND ( tg.endTime > CURRENT_TIME() OR tg.endTime is NULL ) ) AND ( t.status IS NULL OR t.status != $codeSuccess ) ORDER BY tg.priority DESC, t.createdAt ASC" )
    		->setMaxResults( $limit );
		$taskGroups = $query->getResult();
		
		foreach( $taskGroups as $taskGroup )
		{
			$taskGroup->setOutput( $this->output );
			$taskGroup->execute( $this->em, $ignoreFailures );	
		}
	}
	
	private function initializeGroup()
	{
		//Inicjalizacja obiektu grupy i zwrocenie go
		$group = new TaskGroup();
		$group->setIdentifier( $this->currentGroupIdentifier );
		$group->setPriority( 100 );
		$group->setFailuresLimit( 3 );
		return $group;
	}
	
	
	
}