<?php

namespace Smentek\TaskBufferBundle\Entity;

use Symfony\Component\DependencyInjection\ContainerAware;

use Doctrine\ORM\Mapping as ORM;
use Smentek\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Validator\Constraints as Validation;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"task_callable" = "TaskCallable", "task_callable_on_object" = "TaskCallableOnObject"})
 * @ORM\Table(name="bundletaskbuffer_task", indexes={@ORM\index(name="prio", columns={"priority"}), @ORM\index(name="discr", columns={"discr"}), @ORM\index(name="endtime", columns={"end_time"}), @ORM\index(name="starttime", columns={"start_time"}), @ORM\index(name="status", columns={"status"})})
 * @ORM\HasLifecycleCallbacks
 */
abstract class Task extends ContainerAware
{
    const STATUS_AWAITING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_CANCELED = 2;
    const STATUS_INVALID_CALLABACK = 3;
    const STATUS_RUNTIME_EXCEPTION = 4;

    /**
     * @ORM\Id
     * @ORM\Column(name="task_id", type="integer")
     * @ORM\GeneratedValue
     */
    protected $taskId;

    //TODO: Coupling is on base class right now. Consider moving it lower to TaskCallable and TaskCallableOnObject .  
    //http://www.doctrine-project.org/docs/orm/2.0/en/reference/inheritance-mapping.html: 
    //"There is a general performance consideration with Single Table Inheritance: If you use a STI entity as a many-to-one or one-to-one entity you should never use one of the classes at the upper levels of the inheritance hierachy as “targetEntity”, only those that have no subclasses. Otherwise Doctrine CANNOT create proxy instances of this entity and will ALWAYS load the entity eagerly."
    
    /**
     * @ORM\ManyToOne(targetEntity="TaskGroup", inversedBy="tasks")
     * @ORM\JoinColumn(name="task_group_id", referencedColumnName="task_group_id")
     */
    protected $taskGroup;

    /**
     * @ORM\Column(name="callable", type="string", length=255)
     */
    protected $callable;

    /**
     * @ORM\Column(name="duration", type="bigint", nullable=true)
     */
    protected $duration;

    /**
     *
     * 	@var integer $status - AWAITING|SUCCESS|INVALID_CALLABACK|RUNTIME_EXCEPTION
     *
     * @ORM\Column(name="status", type="integer", nullable=false);
     */
    protected $status;

    /**
     * @ORM\Column(name="failures_limit", type="integer")
     */
    protected $failuresLimit;

    /**
     * @ORM\Column(name="failures", type="integer")
     */
    protected $failures;
    
    /**
     * Tasks with higher priority take precedence over tasks with lower priority.
     *
     * @ORM\Column(name="priority", type="integer")
     *
     * @Validation\NotBlank()
     * @Validation\Min(0)
     * @Validation\Max(1000)
     */
    protected $priority;

    /**
     * @ORM\Column(name="start_time", type="time", nullable=true)
     */
    protected $startTime;

    /**
     * @ORM\Column(name="end_time", type="time", nullable=true)
     */
    protected $endTime;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Validation\NotBlank()
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="executed_at", type="datetime", nullable=true)
     */
    protected $executedAt;
    
    /**
     * The string representation.
     *
     * @return string
     */
    public function __toString()
    {
      return "Task: {$this->getTaskId()} '".$this->getCallable()."' class: ".get_class($this);
    }
    
    public abstract function execute($em = null, $mailer = null);

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

    public function statusAsString( $status )
    {
        $statusStringRepresentations = array(
            self::STATUS_AWAITING => 'AWAITING',
            self::STATUS_SUCCESS => 'SUCCESS',
            self::STATUS_CANCELED => 'CANCELED',
            self::STATUS_INVALID_CALLABACK => 'INVALID_CALLABACK',
            self::STATUS_RUNTIME_EXCEPTION => 'RUNTIME_EXCEPTION',
        );
        
        return ( isset( $statusStringRepresentations[$status] ) ) ? $statusStringRepresentations[$status] : '';
    }
    
    /**
     * @ORM\PrePersist
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

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }
    
    public function getFailuresLimit()
    {
        return $this->failuresLimit;
    }

    public function setFailuresLimit($failuresLimit)
    {
        $this->failuresLimit = $failuresLimit;
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function setFailures($failures)
    {
        $this->failures = $failures;
    }
    
    public function prefixMessage()
    {
        return "Task: {$this->getTaskId()} from group: {$this->getTaskGroup()->getIdentifier()}";
    }

    public function executionResult($status)
    {
        $statusAsString = $this->statusAsString($status);
        return "Status: {$statusAsString}";
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
        if (is_callable($callable))
        {
            call_user_func($callable);
            $status = self::STATUS_SUCCESS;
            $this->setStatus($status);
            $this->taskGroup->setFailureOccured(false);
            return true;
        }
        else
        {
            $status = self::STATUS_INVALID_CALLABACK;
            $this->setstatus($status);
            $this->setFailures($this->getFailures() + 1);
            $this->taskGroup->setFailureOccured(true);
            return false;
        }
    }
}