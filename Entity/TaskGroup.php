<?php

namespace Smentek\TaskBufferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="bundletaskbuffer_task_group")
 */
class TaskGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(name="task_group_id", type="integer")
     * @ORM\GeneratedValue
     */
    protected $taskGroupId;

    /**
     * @ORM\Column(name="identifier", type="string", unique=true)
     */
    protected $identifier;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="taskGroup")
     */
    protected $tasks;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive;

    private $output;
    
    private $failureOccured;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }
    
    /**
     * Get taskGroupId
     *
     * @return integer
     */
    public function getTaskGroupId()
    {
      return $this->taskGroupId;
    }
    
    /**
     * Set identifier
     *
     * @param string $identifier
     * @return TaskGroup
     */
    public function setIdentifier($identifier)
    {
      $this->identifier = $identifier;
      return $this;
    }
    
    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
      return $this->identifier;
    }
    
    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return TaskGroup
     */
    public function setIsActive($isActive)
    {
      $this->isActive = $isActive;
      return $this;
    }
    
    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
      return $this->isActive;
    }
    
    /**
     * Add tasks
     *
     * @param Smentek\TaskBufferBundle\Entity\Task $tasks
     */
    public function addTask(\Smentek\TaskBufferBundle\Entity\Task $tasks)
    {
      $this->tasks[] = $tasks;
    }
    
    /**
     * Get tasks
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
      return $this->tasks;
    }

    /**
     * Set output buffer
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
      $this->output = $output;
    }
    
    /**
     * Set if there was a failure
     * @param bool $failureOccured
     */
    public function setFailureOccured($failureOccured)
    {
      $this->failureOccured = $failureOccured;
    }
    
    public function execute($em = null, $stopOnFailure = false)
    {
        $failureOccured = false;
// \Doctrine\Common\Util\Debug::dump($this);
        
        foreach ($this->tasks as $task)
        {
            $timeStart = Tools::timeInMicroseconds();
            
            try
            {
              $message = '';
                $message = $task->execute($em);
              
                $task->setStatus(Task::STATUS_SUCCESS);
                echo "Ok $message";
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
                $message .= "Message: '".$e->getMessage()."'. ";
                $message .= "Duration:  {$task->getDuration()} µs. ";
            }

            if (isset($em))
            {
              $em->persist($task);
              \Doctrine\Common\Util\Debug::dump($task);
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
    
    /**
     * The string representation.
     * 
     * @return string
     */
    public function __toString()
    {
      return "{$this->getTaskGroupId()} {$this->getIdentifier()}";
    }
}