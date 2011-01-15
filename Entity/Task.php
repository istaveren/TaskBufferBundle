<?php 

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\TaskGroup;

/**
 * @orm:Entity
 * @orm:Table(name="bundletaskbuffer_task")
 * @orm:HasLifecycleCallbacks
 */
class Task
{
    /**
     * @orm:Id
     * @orm:Column(name="task_id", type="integer")
     * @orm:GeneratedValue
     */
    protected $taskId;

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
     * @orm:Column(name="object", type="object", nullable="true")
     * 
     * @validation:NotBlank()
     */
    protected $object;

    /**
     * @orm:Column(name="duration", type="integer", nullable="true")
     */
	protected $duration;    

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

    public function setTaskId( $taskId )
    {
    	$this->taskId = $taskId;
    }

    public function getTaskId()
    {
    	return $this->taskId;
    }

    public function setTaskGroup( TaskGroup $taskGroup )
    {
    	$this->taskGroup = $taskGroup;
    }

    public function getTaskGroup()
    {
    	return $this->taskGroup;
    }

    public function setType( $type )
    {
    	$this->type = $type;
    }

    public function getType()
    {
    	return $this->type;
    }    

    public function setGroupIdentifier( $groupIdentifier )
    {
    	$this->groupIdentifier = $groupIdentifier;
    }

    public function getGroupIdentifier()
    {
    	return $this->groupIdentifier;
    }    

    public function setCallable( $callable )
    {
    	$this->callable = $callable;
    }

    public function getCallable()
    {
    	return $this->callable;
    }

    public function setObject( $object )
    {
    	$this->object = $object;
    }

    public function getObject()
    {
    	return $this->object;
    }

    public function setDuration( $duration )
    {
    	$this->duration = $duration;
    }

    public function getDuration()
    {
    	return $this->duration;
    }

    public function getFailCount()
    {
    	return $this->failCount;
    }

    public function setFailCount( $failCount )
    {
    	$this->failCount = $failCount;
    }

    public function getCreatedAt()
    {
    	return $this->createdAt;
    }

    public function setCreatedAt( $createdAt )
    {
    	$this->createdAt = $createdAt;
    }

	/** 
	 * @orm:PrePersist 
	 */
    public function autogenerateCreatedAt()
    {
    	if( !isset( $this->createdAt ) )
    	{
    		$this->createdAt = date_create( "now" );	
    	}
    }

    public function setExecutedAt( $executedAt )
    {
    	$this->executedAt = $executedAt;
    }

    public function getExecutedAt()
    {
    	return $this->executedAt;
    }    
    
    public function getFailuresCount()
    {
    	return $this->failuresCount;
    }    

    public function setFailuresCount( $failuresCount )
    {
    	$this->failuresCount = $failuresCount;
    }    
}
