<?php

namespace Smentek\TaskBufferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints as Validation;

/**
 * @ORM\Entity
 */
class TaskCallableOnObject extends Task
{
    /**
     * @ORM\Column(name="object", type="object", nullable=true)
     *
     * @Validation\NotBlank()
     */
    protected $object;
    
    public function execute($em = null)
    {
        $timeStart = Tools::timeInMicroseconds();
        
        $obj = $this->getObject();
        if ($em && method_exists($obj, 'setEntityManager'))
        {
          $obj->setEntityManager($em);
        }
        
        $this->call(array( $this->getObject(), $this->getCallable())); 
        
        return $this->postExecute($timeStart);
    }

    /**
     * Set object
     *
     * @param object $object
     * @return TaskCallableOnObject
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

    /**
     * Get object
     *
     * @return object 
     */
    public function getObject()
    {
        return $this->object;
    }
}