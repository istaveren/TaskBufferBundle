<?php

namespace Smentek\TaskBufferBundle\Entity;

use Smentek\TaskBufferBundle\Entity\Task;
use Smentek\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @orm:Entity
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
