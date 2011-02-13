<?php

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\TaskGroup;

/**
 * @orm:Entity
 * @orm:InheritanceType("SINGLE_TABLE")
 * @orm:DiscriminatorColumn(name="discr", type="string")
 * @orm:DiscriminatorMap({"task_callable" = "TaskCallable", "task_callable_on_object" = "TaskCallableOnObject"})
 * @orm:Table(name="bundletaskbuffer_task")
 * @orm:HasLifecycleCallbacks
 */
class Task
{
    const STATUS_AWAITING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_INVALID_CALLABACK = 2;
    const STATUS_RUNTIME_EXCEPTION = 3;

    /**
     * @orm:Id
     * @orm:Column(name="task_id", type="integer")
     * @orm:GeneratedValue
     */
    protected $taskId;

    //TODO: Coupling is on base class right now. Consider moving it lower to TaskCallable and TaskCallableOnObject .  
    //http://www.doctrine-project.org/docs/orm/2.0/en/reference/inheritance-mapping.html: 
    //"There is a general performance consideration with Single Table Inheritance: If you use a STI entity as a many-to-one or one-to-one entity you should never use one of the classes at the upper levels of the inheritance hierachy as “targetEntity”, only those that have no subclasses. Otherwise Doctrine CANNOT create proxy instances of this entity and will ALWAYS load the entity eagerly."
    
    /**
     * @orm:ManyToOne(targetEntity="TaskGroup", inversedBy="tasks", cascade={"persist"})
     * @orm:JoinColumn(name="task_group_id", referencedColumnName="task_group_id")
     */
    protected $taskGroup;

    /**
     * @orm:Column(name="callable", type="string", length="255")
     */
    protected $callable;

    /**
     * @orm:Column(name="duration", type="bigint", nullable="true")
     */
    protected $duration;

    /**
     *
     * 	@var integer $status - AWAITING|SUCCESS|INVALID_CALLABACK|RUNTIME_EXCEPTION
     *
     * @orm:Column(name="status", type="integer", nullable="false");
     */
    protected $status;

    /**
     * @orm:Column(name="failures_count", type="integer")
     */
    protected $failuresCount;

    /**
     * @orm:Column(name="created_at", type="datetime")
     *
     * @validation:NotBlank()
     */
    protected $createdAt;

    /**
     * @orm:Column(name="executed_at", type="datetime", nullable="true")
     */
    protected $executedAt;

    public function __construct(){}

    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
    }

    public function getTaskId()
    {
        return $this->taskId;
    }

    public function setTaskGroup(TaskGroup $taskGroup)
    {
        $this->taskGroup = $taskGroup;
    }

    public function getTaskGroup()
    {
        return $this->taskGroup;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setGroupIdentifier($groupIdentifier)
    {
        $this->groupIdentifier = $groupIdentifier;
    }

    public function getGroupIdentifier()
    {
        return $this->groupIdentifier;
    }

    public function setCallable($callable)
    {
        $this->callable = $callable;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @orm:PrePersist
     */
    public function autogenerateCreatedAt()
    {
        if ( !isset( $this->createdAt ) )
        {
            $this->createdAt = date_create( "now" );
        }
    }

    public function setExecutedAt($executedAt)
    {
        $this->executedAt = $executedAt;
    }

    public function getExecutedAt()
    {
        return $this->executedAt;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getFailuresCount()
    {
        return $this->failuresCount;
    }

    public function setFailuresCount($failuresCount)
    {
        $this->failuresCount = $failuresCount;
    }

    public function prefixMessage()
    {
        return "Task: {$this->getTaskId()} from group: {$this->getTaskGroup()->getTaskGroupId()}.";
    }

    public function executionResult($status)
    {
        return "[Status: $status]";
    }
	
    public function postExecute($timeStart)
    {
        $this->setExecutedAt(date_create( "now" ));
        
        $timeEnd = Tools::timeInMicroseconds();
        $microseconds = (int)(($timeEnd-$timeStart)*1000000);

        $this->setDuration( $microseconds );
        
        $message = "{$this->prefixMessage()} {$this->executionResult($this->getStatus())}. ";        
        $message .= "Duration:  {$this->getDuration()} µs.";
        
        return $message;
	}    
	
    public function call($callable)
    {
        if (is_callable($callable ))
        {
            call_user_func($callable);
            $status = self::STATUS_SUCCESS;
            $this->setStatus($status);
        }
        else
        {
            $status = self::STATUS_INVALID_CALLABACK;
            $this->setstatus($status);
            $this->setFailuresCount($this->getFailuresCount() + 1);
        }
    }
}
