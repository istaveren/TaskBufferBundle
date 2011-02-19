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

    private $limit = 10;

    private $priority = 100;

    private $failuresLimit = 3;
    
    private $startTime;
    
    private $endTime;

    public function __construct($em)
    {
        $this->em = $em;
        $this->groups = new ArrayCollection();
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
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

    public function time($startTime, $endTime = null)
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
        $taskGroup = $this->getCurrentGroup();
        $taskGroup->setFailuresLimit($failuresLimit);
        return $this;
    }
    
    public function activate()
    {
        $taskGroup = $this->getCurrentGroup();
        $taskGroup->setIsActive(true);
        return $this;        
    }

    public function deactivate()
    {
        $taskGroup = $this->getCurrentGroup();
        $taskGroup->setIsActive(false);
        return $this;        
    }
        
    public function flush()
    {
        $this->em->persist($this->getCurrentGroup());
        $this->em->flush();
    }

    public function queue($callable, $groupIdentifier = 'standard')
    {
        $this->initialize($callable, $groupIdentifier);
        $this->flush();
//        $this->em->persist($this->initialize($callable, $groupIdentifier));
//        $this->em->flush();
    }

    public function initialize($callable, $groupIdentifier = 'standard')
    {
        $this->currentGroupIdentifier = $groupIdentifier;
        $this->setGroupByCurrentIdentifier();

        $group = (is_array($callable)) ? $this->initializeTaskForObject($callable) : $this->initializeTaskForMethod($callable);

        return $group;
    }

    public function initializeTaskForObject($callableWithObject)
    {
        $this->checkArrayForCallableWithObject($callableWithObject);
        $object = $callableWithObject[0];
        $callable = $callableWithObject[1];

        //TODO: initialization form IoC Container!
        $task = new TaskCallableOnObject();
        $task = $this->initializeTask($task);
        $task->setCallable($callable);
        $task->setObject($object);

        return $this->groups->get($this->currentGroupIdentifier);
    }

    public function initializeTaskForMethod($callable)
    {
        //TODO: initialization form IoC Container!
        $task = new TaskCallable();
        $task = $this->initializeTask($task);
        $task->setCallable($callable);

        return $this->groups->get($this->currentGroupIdentifier);
    }

    public function pull($ignoreFailures)
    {
        $this->em->getConnection()->beginTransaction();
        try
        {
            $codeSuccess = Task::STATUS_SUCCESS;
            $query = $this->em->createQuery("SELECT t, tg FROM \Smentek\TaskBufferBundle\Entity\TaskGroup tg JOIN tg.tasks t WHERE tg.failuresLimit > t.failuresCount AND ( ( tg.startTime < CURRENT_TIME() OR tg.startTime is NULL ) AND ( tg.endTime > CURRENT_TIME() OR tg.endTime is NULL ) ) AND ( t.status IS NULL OR t.status != {$codeSuccess} ) ORDER BY tg.priority DESC, t.createdAt ASC" )
            ->setMaxResults($this->limit);

            $query->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
            $taskGroups = $query->getResult();

            foreach($taskGroups as $taskGroup)
            {
                $taskGroup->setOutput($this->output);
                $taskGroup->execute($this->em, $ignoreFailures);
                $this->em->getConnection()->commit();
            }

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
        $task->setFailuresCount(0);
        $task->setStatus(Task::STATUS_AWAITING);
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
        $group->setPriority($this->getPriority());
        $group->setFailuresLimit($this->getFailuresLimit());
        $group->setIsActive(true);
        $group->setStartTime($this->startTime);
        $group->setEndTime($this->endTime);
        return $group;
    }

    private function initializeGroup()
    {
        $group = new TaskGroup();
        $group->setIdentifier($this->currentGroupIdentifier);
        $group->setPriority($this->getPriority());
        $group->setFailuresLimit($this->getFailuresLimit());
        $group->setIsActive(true);
        $group->setStartTime($this->startTime);
        $group->setEndTime($this->endTime);

        return $group;
    }

}