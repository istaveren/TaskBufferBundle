<?php

namespace Bundle\TaskBufferBundle\Entity;

use Bundle\TaskBufferBundle\Entity\Task;
use Bundle\TaskBufferBundle\Entity\TaskGroup;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @orm:Entity
 */
class TaskCallable extends Task
{

    public function execute()
    {
        $timeStart = Tools::timeInMicroseconds();
         
        $this->call( $this->getCallable() ); 
                 
        return $this->postExecute( $timeStart );
        
    }
}
