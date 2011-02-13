<?php

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\Task;
use Bundle\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @orm:Entity
 */
class TaskCallableOnObject extends Task
{
    /**
     * @orm:Column(name="object", type="object", nullable="true")
     *
     * @validation:NotBlank()
     */
    protected $object;
    
    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function execute()
    {
        $timeStart = Tools::timeInMicroseconds();
        $this->call(array( $this->getObject(), $this->getCallable())); 
        
        return $this->postExecute($timeStart);
    }
}
