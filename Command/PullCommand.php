<?php
namespace Bundle\TaskBufferBundle\Command;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PullCommand extends Command
{
	protected function configure()
	{
	    $this->setName( 'task-buffer:pull' )
			->setDescription( 'Pull tasks from Task Buffer and execute them one by one.' )
	        ->setDefinition(array(
	            new InputArgument(
	                'limit', 
	                InputArgument::OPTIONAL, 
	                'Limit of tasks to pulled from Task Buffer and executed.'
	            ),
	            new InputArgument( 
	            	'ignore-failures', 
	            	InputArgument::OPTIONAL, 
	            	'If false Task Buffer will not proceed in case of errors.'),
	    	
		));
	}
	
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract class is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	//TODO:          
         $ignoreFailures = $input->getArgument('ignore-failures');

         $limit = $input->getArgument('limit');
         if( !isset( $limit ) )
         {
         	$limit = ( !isset( $limit ) ) ? 10 : $limit;
         }
         
         $taskManager = $this->get( 'task_buffer.task_manager' );
         $messages = $taskManager->pull( $limit, $ignoreFailures );
         
         
		
         
         
		//$output->setDecorated( true ); 
        $output->write( ":) {$limit}", 1 );
		
         
    }
	
	
}
