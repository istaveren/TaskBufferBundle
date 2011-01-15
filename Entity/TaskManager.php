<?php

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\Task;
use Bundle\TaskBufferBundle\Entity\TaskGroup;
use Bundle\TaskBufferBundle\Entity\TaskBufferException;
use Doctrine\Common\Collections\ArrayCollection;

class TaskManager
{
	private $em;
	
	private $groups;
	
	private $currentGroupIdentifier;

	public function __construct( $em )
	{
		$this->em = $em;
		$this->groups = new ArrayCollection();
	} 
	
	public function queue( $callable, $groupIdentifier = 'standard' )
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
		if( !isset( $callableWithObject[0] ) || !isset( $callableWithObject[0] ) )
		{
			throw new TaskBufferException( "Callable improperly set!" );
		}
		
		$object = $callableWithObject[0];
		$callable = $callableWithObject[1];
		
		$task = new Task();
		$task->setCallable( $callable );
		$task->setObject( $object );
		$task->setFailuresCount( 0 );
		
		$this->groups->get( $this->currentGroupIdentifier )->addTask( $task );
		$task->setTaskGroup( $this->groups->get( $this->currentGroupIdentifier ) );
		return $this->groups->get( $this->currentGroupIdentifier );
	}
	 
	public function initializeTaskForMethod( $callable )
	{
		$task = new Task();
		$task->setCallable( $callable );
		$task->setFailuresCount( 0 );
		$this->groups->get( $this->currentGroupIdentifier )->addTask( $task );
		$task->setTaskGroup( $this->groups->get( $this->currentGroupIdentifier ) );
		return $this->groups->get( $this->currentGroupIdentifier );
	}
	
	private function initializeGroup()
	{
		//Inizjalizacja obiketu grupy i zwrocenie go
		$group = new TaskGroup();
		$group->setIdentifier( $this->currentGroupIdentifier );
		$group->setPriority( 100 );
		$group->setFailuresLimit( 3 );
		return $group;
	}
	
}