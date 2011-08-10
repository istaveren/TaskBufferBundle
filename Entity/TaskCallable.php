<?php

namespace Smentek\TaskBufferBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @ORM\Entity
 */
class TaskCallable extends Task
{
    public function execute()
    {
        $timeStart = Tools::timeInMicroseconds();
        $this->call($this->getCallable()); 
                 
        return $this->postExecute($timeStart);
    }
}
