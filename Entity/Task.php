<?php 

namespace Bundle\TaskBufferBundle\Entity;

/**
 * @orm:Entity
 */
class Task
{
    /**
     * @orm:Id
     * @orm:Column(name="task_id", type="integer")
     * @orm:GeneratedValue(strategy="IDENTITY")
     */
    protected $taskId;

    /**
     * @orm:Column(name="type", type="integer")
     * 
     * @validation:NotBlank()
     */
    protected $type;
    
    /**
     * @orm:Column(name="group_identifier", type="string", length="255")
     * 
     * @validation:NotBlank()
     */
    protected $groupIdentifier;
    
    /**
     * @orm:Column(name="callable", type="string", length="255")
     */
    protected $callable;
    
    /**
     * @orm:Column(name="object", type="object")
     * 
     * @validation:NotBlank()
     */
    protected $object;
    
    /**
     * @orm:Column(name="priority", type="integer")
     * 
     * @validation:NotBlank()
     * @validation:Min(0) 
     * @validation:Max(1000)
     */
    protected $priority;

    /**
     * @orm:Column(name="duration", type="integer")
     */
	protected $duration;    

    /**
     * @orm:Column(name="fail_count", type="integer")
     */
	protected $failCount;    
		
    /** 
     * @orm:Column(name="created_at", type="datetime") 
     * 
     * @validation:NotBlank()
     */
    protected $createdAt;

    /** 
     * @orm:Column(name="executed_at", type="datetime") 
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
        
    public function setExecutedAt( $executedAt )
    {
    	$this->executedAt = $executedAt;
    }
    
    public function getExecutedAt()
    {
    	return $this->executedAt;
    }    
}

