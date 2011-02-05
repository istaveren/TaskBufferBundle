<?php

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Console\Output\OutputInterface;

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

    private $output;

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

    public function setDuration( $duration )
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

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus( $status )
    {
        $this->status = $status;
    }

    public function getFailuresCount()
    {
        return $this->failuresCount;
    }

    public function setFailuresCount( $failuresCount )
    {
        $this->failuresCount = $failuresCount;
    }

    public function setOutput( OutputInterface $output )
    {
        $this->output = $output;
    }

    //TODO: Dwa taski dziedziczae po jednej klasie na wspolnej tabeli z jedna metoda call.
            
//    public function execute()
//    {
//        $timeStart = Tools::timeInMicroseconds();
//         
//        $object = $this->getObject();
//        $message = ( !isset( $object ) ) ?
//            $this->callCallable( $timeStart ) :
//            $this->callCallableOnObject( $timeStart );
//         
//        $this->setExecutedAt( date_create( "now" ) );
//        
//        $timeEnd = Tools::timeInMicroseconds();
//        $microseconds = (int)( ( $timeEnd - $timeStart ) * 1000000 );
//
//        $this->setDuration( $microseconds );
//        
//        $message .= "Duration:  {$this->getDuration()} µs.";
//
//        if( isset( $this->output ) )
//        {
//            $this->output->write( $message, 1 );
//        }        
//        
//        return $message;
//    }

    
//    private function callCallable( $timeStart )
//    {
//        if( is_callable( $this->getCallable() ) )
//        {
//            call_user_func( $this->getCallable() );
//            $status = self::STATUS_SUCCESS;
//            $this->setStatus( $status );
//            $message = "{$this->prefixMessage()} {$this->executionResult( $status )}. ";
//        }
//        else
//        {
//            $status = self::STATUS_INVALID_CALLABACK;
//            $this->setstatus( $status );
//            $message = "{$this->prefixMessage()} status: {$status}! ";
//            $this->setFailuresCount( $this->getFailuresCount() + 1 );
//        }
//        
//        return $message; 
//    }

//    private function callCallableOnObject( $timeStart )
//    {
//         
//        if( is_callable( array( $this->getObject(), $this->getCallable() ) ) )
//        {
//            call_user_func( array( $this->getObject(), $this->getCallable() ) );
//             
//            $status = self::STATUS_SUCCESS;
//            $this->setStatus( $status );
//            $message = "{$this->prefixMessage()} {$this->executionResult( $status )}. ";
//        }
//        else
//        {
//            $status = self::STATUS_INVALID_CALLABACK;
//            $this->setstatus( $status );
//            $message = "{$this->prefixMessage()} {$this->executionResult( $status )}. ";
//            $this->setFailuresCount( $this->getFailuresCount() + 1 );
//        }
//
//        return $message;
//    }

    public function prefixMessage()
    {
        return "Task: {$this->getTaskId()} from group: {$this->getTaskGroup()->getTaskGroupId()}.";
    }

    public function executionResult( $status )
    {
        return "[Status: $status]";
    }
}
