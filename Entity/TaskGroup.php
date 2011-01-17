<?php

namespace Bundle\TaskBufferBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @orm:Entity
 * @orm:Table(name="bundletaskbuffer_task_group")
 */
class TaskGroup
{
    /**
     * @orm:Id
     * @orm:Column(name="task_group_id", type="integer")
     * @orm:GeneratedValue
     */
	protected $taskGroupId;

    /**
     * @orm:Column(name="identifier", type="string", unique="true")
     */
	protected $identifier;

    /**
     * @orm:OneToMany(targetEntity="Task", mappedBy="taskGroup", cascade={"persist"})
     */
    private $tasks;

    /**
     * Tasks with higher priority take precedence over tasks with lower priority. 
     * 
     * @orm:Column(name="priority", type="integer")
     * 
     * @validation:NotBlank()
     * @validation:Min(0) 
     * @validation:Max(1000)
     */
    protected $priority;

    /**
     * @orm:Column(name="start_time", type="datetime", nullable="true")
     */
    protected $startTime;

    /**
     * @orm:Column(name="end_time", type="datetime", nullable="true")
     */
    protected $endTime;

    /**
     * @orm:Column(name="failures_limit", type="integer")
     */    
    protected $failuresLimit;

    public function __construct()
    {
 		$this->tasks = new ArrayCollection();
    }

    public function setTaskGroupId( $taskGroupId )
    {
    	$this->taskGroupId = $taskGroupId;
    }

    public function getTaskGroupId()
    {
    	return $this->taskGroupId;
    }

    public function setIdentifier( $identifier )
    {
    	$this->identifier = $identifier;
    }

    public function getidentifier()
    {
    	return $this->identifier;
    }    

    public function setTasks( $tasks )
    {
    	$this->tasks = $tasks;
    }

    public function getTasks()
    {
    	return $this->tasks;
    }    

    public function setPriority( $priority )
    {
    	$this->priority = $priority;
    }

    public function getPriority()
    {
    	return $this->priority;
    }    

    public function setStartTime( $startTime )
    {
    	$this->startTime = $startTime;
    }

    public function getStartTime()
    {
    	return $this->startTime;
    }    

    public function setEndTime( $endTime )
    {
    	$this->endTime = $endTime;
    }

    public function getEndTime()
    {
    	return $this->endTime;
    }    

    public function addTask( Task $task )
    {
    	$this->tasks->add( $task );
    }

    public function getFailuresLimit()
    {
    	return $this->failuresLimit;
    }    

    public function setFailuresLimit( $failuresLimit )
    {
    	$this->failuresLimit = $failuresLimit;
    }    
    
    public function execute( $ignoreFailures )
    {
    	//TODO: $ignoreFailures == false brake execution on any error!
    	$messages = array();
    	foreach( $this->tasks as $task )
    	{
			$timeStart = microtime();				
				
			try 
			{
    			$message = $task->execute( $timeStart );
    		}
    		catch( Exception $e )
    		{
				$task->setFailuresCount( $this->getFailuresCount() + 1 );
				$task->setErrorCode( self::ERROR_CODE_RUNTIME_EXCEPTION );
				$this->setExecutedAt( date_create( "now" ) );
				$this->setDuration( microtime() - $timeStart );
				$errorCode = self::ERROR_CODE_RUNTIME_EXCEPTION;
				$message = "{$task->prefixMessage()} Error code: {$errorCode}!";
    		}
    		$messages = $message;
    	}
    	return $messages;
    }
}
