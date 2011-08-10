<?php

namespace Smentek\TaskBufferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @ORM\Entity
 */
class TaskCallableOnObject extends Task
{
    /**
     * @ORM\Column(name="object", type="object", nullable="true")
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
