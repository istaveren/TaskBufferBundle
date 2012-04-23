<?php

namespace Smentek\TaskBufferBundle\Entity;

use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskGroup;
use Smentek\TaskBufferBundle\Entity\TaskBufferException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;

class TaskBuffer
{
    private $em;

    private $groups;

    private $currentGroupIdentifier;

    private $output;

    private $pullLimit = 10;

    private $priority = 100;

    private $failuresLimit = 3;
    
    private $startTime;
    
    private $endTime;
    
    private $groupIdentifier = 'standard';

    public function __construct($em)
    {
        $this->em = $em;
        $this->groups = new ArrayCollection();
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setPullLimit($pullLimit)
    {
        $this->pullLimit = $pullLimit;
    }

    public function getPullLimit()
    {
        return $this->pullLimit;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setGroupIdentifier($groupIdentifier)
    {
        $this->groupIdentifier = $groupIdentifier;
    }

    public function getGroupIdentifier()
    {
        return $this->groupIdentifier;
    }
        
    public function setFailuresLimit($failuresLimit)
    {
        $this->failuresLimit = $failuresLimit;
    }

    public function getFailuresLimit()
    {
        return $this->failuresLimit;
    }

    private function getCurrentGroup()
    {
        return $this->groups->get($this->currentGroupIdentifier);
    }

    public function priority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Sets window for task execution.
     *  
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     */
    public function time( \DateTime $startTime, \DateTime $endTime = null)
    {
        if (!$startTime instanceof \DateTime and !$endTime instanceof \DateTime)
        {
            return $this;
        }
        
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        
        return $this;
    }

    public function failuresLimit($failuresLimit)
    {
        $this->failuresLimit = $failuresLimit;
        
        return $this;
    }
    
    public function flush()
    {
        $this->em->persist($this->getCurrentGroup());
        $this->em->flush();
    }

    public function queue($callable, $groupIdentifier = '' )
    {
        $this->initialize($callable, $groupIdentifier);
        $this->flush();
    }

    public function initialize($callable, $groupIdentifier = '' )
    {
        $this->currentGroupIdentifier = $this->determineGroupIdentifier( $groupIdentifier );
        $this->setGroupByCurrentIdentifier();

        $group = (is_array($callable)) ? $this->initializeTaskForObject($callable) : $this->initializeTaskForMethod($callable);

        return $group;
    }
    
    private function determineGroupIdentifier( $groupIdentifier )
    {
        return ( $groupIdentifier != '' ) ? $groupIdentifier : $this->getGroupIdentifier();
    }

    public function initializeTaskForObject($callableWithObject)
    {
        $this->checkArrayForCallableWithObject($callableWithObject);
        $object = $callableWithObject[0];
        $callable = $callableWithObject[1];

        $task = new TaskCallableOnObject();
        $task = $this->initializeTask($task);
        $task->setCallable($callable);
        $task->setObject($object);

        return $this->groups->get($this->currentGroupIdentifier);
    }

    public function initializeTaskForMethod($callable)
    {
        $task = new TaskCallable();
        $task = $this->initializeTask($task);
        $task->setCallable($callable);

        return $this->groups->get($this->currentGroupIdentifier);
    }

    public function pull($stopOnFailure)
    {
        $this->em->getConnection()->beginTransaction();
        try
        {
            $codeSuccess = Task::STATUS_SUCCESS;
            $query = $this->em->createQuery("SELECT t, tg FROM \Smentek\TaskBufferBundle\Entity\TaskGroup tg 
                                             JOIN tg.tasks t 
                                             WHERE t.failuresLimit > t.failures AND ( ( t.startTime < CURRENT_TIME() OR t.startTime is NULL ) AND ( t.endTime > CURRENT_TIME() OR t.endTime is NULL ) ) AND ( t.status IS NULL OR t.status != {$codeSuccess} ) ORDER BY t.priority DESC, t.createdAt ASC" )
              ->setMaxResults($this->pullLimit);

            $query->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
            $taskGroups = $query->getResult();

            foreach($taskGroups as $taskGroup)
            {
                $taskGroup->setOutput($this->output);
                $taskGroup->execute($this->em, $stopOnFailure);
            }
            $this->em->getConnection()->commit();
        }
        catch (Exception $e)
        {
            $this->em->getConnection()->rollback();
            $this->em->close();
            throw $e;
        }
    }

    private function setGroupByCurrentIdentifier()
    {
        if (!$this->groups->containsKey($this->currentGroupIdentifier))
        {
            $groups = $this->em->createQuery("SELECT tg FROM Smentek\TaskBufferBundle\Entity\TaskGroup tg WHERE tg.identifier = '$this->currentGroupIdentifier'")->getResult();
            $group = (isset($groups[0]) && $groups[0] instanceof TaskGroup) ? $this->actualizeGroup( $groups[0] ) : $this->initializeGroup();
            $this->groups->set($this->currentGroupIdentifier, $group);
        }
    }

    private function initializeTask($task)
    {
        $task->setFailuresLimit($this->failuresLimit);
        $task->setStatus(Task::STATUS_AWAITING);
        
        $task->setPriority($this->getPriority());
        $task->setFailuresLimit($this->getFailuresLimit());
        $task->setFailures(0);
        $task->setStartTime($this->startTime);
        $task->setEndTime($this->endTime);
        
        
        $this->groups->get($this->currentGroupIdentifier)->addTask($task);
        $task->setTaskGroup($this->groups->get($this->currentGroupIdentifier));

        return $task;
    }

    private function checkArrayForCallableWithObject($callableWithObject)
    {
        if (!isset($callableWithObject[0]) || !isset($callableWithObject[1]))
        {
            throw new TaskBufferException("There is no callable!");
        }
    }
    
    private function actualizeGroup( $group )
    {
        $group->setIsActive(true);
        
        return $group;
    }

    private function initializeGroup()
    {
        $group = new TaskGroup();
        $group->setIdentifier($this->currentGroupIdentifier);
        $group->setIsActive(true);

        return $group;
    }

}