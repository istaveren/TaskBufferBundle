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
         
        $message = $this->call( $timeStart ); 
                 
        $this->setExecutedAt( date_create( "now" ) );
        
        $timeEnd = Tools::timeInMicroseconds();
        $microseconds = (int)( ( $timeEnd - $timeStart ) * 1000000 );

        $this->setDuration( $microseconds );
        
        $message .= "Duration:  {$this->getDuration()} µs.";

        if( isset( $this->output ) )
        {
            $this->output->write( $message, 1 );
        }        
        
        return $message;
    }
    
    
    private function call( $timeStart )
    {
        if( is_callable( $this->getCallable() ) )
        {
            call_user_func( $this->getCallable() );
            $status = self::STATUS_SUCCESS;
            $this->setStatus( $status );
            $message = "{$this->prefixMessage()} {$this->executionResult( $status )}. ";
        }
        else
        {
            $status = self::STATUS_INVALID_CALLABACK;
            $this->setstatus( $status );
            $message = "{$this->prefixMessage()} status: {$status}! ";
            $this->setFailuresCount( $this->getFailuresCount() + 1 );
        }
        
        return $message; 
    }
    
}
