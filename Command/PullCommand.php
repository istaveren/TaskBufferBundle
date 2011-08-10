<?php
namespace Smentek\TaskBufferBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

use \Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class PullCommand extends ContainerAwareCommand
{
	protected function configure()
	{
	    $this->setName( 'task-buffer:pull' )
			->setDescription( 'Pull tasks from Task Buffer and execute them one by one.' )
	        ->setDefinition( array(
	            new InputArgument(
	                'limit', 
	                InputArgument::OPTIONAL, 
	                'Quantity of tasks executed at once.'
	            ),
	            new InputOption(
					'stop-on-failure', 
	            	InputArgument::OPTIONAL, 
	            	InputOption::VALUE_NONE,
	            	'If option is in use Task Buffer will not proceed in case of errors.'
	            ),
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
        $stopOnFailure = $input->getOption('stop-on-failure');
        $limitFromInput = $input->getArgument('limit');
        $pullLimit = (!isset($limitFromInput)) ? $this->container->getParameter('task_buffer.pull_limit') : $limitFromInput;
         
        $taskBuffer = $this->getContainer()->get('task_buffer');
        $taskBuffer->setOutput($output);
        $taskBuffer->setPullLimit($pullLimit);
        $taskBuffer->pull($stopOnFailure);
    }
}