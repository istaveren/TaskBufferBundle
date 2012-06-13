<?php

namespace Smentek\TaskBufferBundle\Entity;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

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
    
    public function execute($em = null, $mailer = null)
    {
        $timeStart = Tools::timeInMicroseconds();
        
        $obj = $this->getObject();
        
        if ($obj instanceof \Swift_Message
            &&
            $mailer)
        {
          $mailer->send($obj);
          $this->setStatus(self::STATUS_SUCCESS);
          $this->taskGroup->setFailureOccured(false);
        }
        else 
        {
          if ($em && method_exists($obj, 'setEntityManager'))
          {
            $obj->setEntityManager($em);
          }
          if ($mailer && method_exists($obj, 'setMailer'))
          {
            $obj->setMailer($mailer);
          }
          if (method_exists($obj, 'setQueueCreate'))
          {
            $obj->setQueueCreate($this->getCreatedAt());
          }
          if ($obj instanceof ContainerAwareInterface)
          {
            $obj->setContainer($this->container);
          }
          
          $this->call(array($obj, $this->getCallable()));
        } 
        
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