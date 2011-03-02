<?php

namespace Smentek\TaskBufferBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @orm:OneToMany(targetEntity="Task", mappedBy="taskGroup", cascade={"persist","remove"})
     */
    protected $tasks;

    /**
     * @orm:Column(name="is_active", type="boolean")
     */
    protected $isActive;

    private $output;
    
    private $failureOccured;

    public function __construct()  
    {
        $this->tasks = new ArrayCollection();
    }

    public function setTaskGroupId($taskGroupId)
    {
        $this->taskGroupId = $taskGroupId;
    }

    public function getTaskGroupId()
    {
        return $this->taskGroupId;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getidentifier()
    {
        return $this->identifier;
    }

    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }

    public function getTasks()
    {
        return $this->tasks;
    }

    public function addTask(Task $task)
    {
        $this->tasks->add($task);
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setFailureOccured( $failureOccured )
    {
        $this->failureOccured = $failureOccured;
    }
    
    public function execute($em = null, $stopOnFailure = false)
    {
        $failureOccured = false;

        foreach ($this->tasks as $task)
        {
            $timeStart = Tools::timeInMicroseconds();

            try
            {
                $message = $task->execute();
                $task->setStatus(Task::STATUS_SUCCESS);
            }
            catch(\Exception $e)
            {
                $this->failureOccured = true;
                
                $task->setFailures($task->getFailures() + 1);
                $task->setStatus(Task::STATUS_RUNTIME_EXCEPTION);
                $task->setExecutedAt(date_create("now"));
                $task->setDuration(microtime() - $timeStart);
                $status = Task::STATUS_RUNTIME_EXCEPTION;
                $message = "{$task->prefixMessage()} {$task->executionResult($status)}. ";
                $message .= "Duration:  {$task->getDuration()} µs. ";
            }

            if (isset($em))
            {
                $em->persist($task);
                $em->flush();
            }

            if ( $stopOnFailure && $this->failureOccured )
            {
                $message .= "\nExecution stopped because of failure occurrence.";
            }
            
            if (isset($this->output))
            {
                $this->output->write($message, 1);
            }
            
            if ( $stopOnFailure && $this->failureOccured )
            {
                break;
            }
            
        }
        
    }
}
