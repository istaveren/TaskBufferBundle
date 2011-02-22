<?php

namespace Smentek\TaskBufferBundle\Entity;

use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskGroup;
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
sleep(3);        
        return $this->postExecute($timeStart);
    }
}
