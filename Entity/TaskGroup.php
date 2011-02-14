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
     * @orm:OneToMany(targetEntity="Task", mappedBy="taskGroup", cascade={"persist"})
     */
    protected $tasks;

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

    /**
     * @orm:Column(name="is_active", type="boolean")
     */
    protected $isActive;

    private $output;

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

    public function addTask(Task $task)
    {
        $this->tasks->add($task);
    }

    public function getFailuresLimit()
    {
        return $this->failuresLimit;
    }

    public function setFailuresLimit($failuresLimit)
    {
        $this->failuresLimit = $failuresLimit;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActivet = $isActive;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute($em = null, $ignoreFailures = true)
    {
        //TODO: $ignoreFailures == false brake execution on any error!
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
                $task->setFailuresCount($task->getFailuresCount() + 1);
                $task->setStatus(Task::STATUS_RUNTIME_EXCEPTION);
                $task->setExecutedAt(date_create("now"));
                $task->setDuration(microtime() - $timeStart);
                $status = Task::STATUS_RUNTIME_EXCEPTION;
                $message = "{$task->prefixMessage()} {$task->executionResult($status)}. ";
                $message .= "Duration:  {$task->getDuration()} µs.";
            }

            if (isset($em))
            {
                $em->persist($task);
                $em->flush();
            }

            if (isset($this->output))
            {
                $this->output->write($message, 1);
            }
        }
        
    }
}
