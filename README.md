Provides simple queuing for Symfony2 projects.

Work in progress... Bundle not stable yet!

Installation
============

For: https://github.com/symfony/symfony-sandbox rev: 00c0e57a93ba8407dd82. 

  1. Add this bundle to your src/ dir

         $ git submodule add git://github.com/smentek/TaskBufferBundle.git src/Smentek/TaskBufferBundle

  2. Add the Smentek namespace to your autoloader:

         // app/autoload.php
         $loader->registerNamespaces(array(
             'Smentek' => __DIR__.'/../src',
             // your other namespaces
         ));

  3. Add this bundle to your application's kernel (app/AppKernel.php):

         public function registerBundles()
         {
             return array(
                 //...
                 new Smentek\TaskBufferBundle\TaskBufferBundle(),
                 //...
             );
         }

  3. Configure the routing (app/config/routing.yml):
        
         task_buffer:
             resource: @TaskBufferBundle/Resources/config/routing.yml

  4. Configure the service in your config (config/app/config.yml):

         ## Doctrine Configuration
         doctrine:
             dbal:
                 dbname:   symfony-sandbox
                 user:     dbuser
                 password: dbpass
             orm:
                 auto_generate_proxy_classes: %kernel.debug%
                 mappings:
                     TaskBufferBundle: ~                     

  5. Configure tests if you are interested in them (app/phpunit.xml.dist):

         <testsuites>
             <testsuite name="Project Test Suite">
                 ...
                 <directory>../src/Bundle/*/Tests</directory>
                  ...
             </testsuite>
         </testsuites>


API
----------

queue( callable [, group_identifier] ) - Store task in a buffer.
	
failuresLimit( int limit ) - Limit of tries for task execution.
	
priority( int priority ) - Tasks with lower number goes first.
     
time( DateTime $timeFrom, DateTime $timeUntil ) - Task execution occur only between specified hours.


Use examples
----------


    public function someAction()
    {
        ...

        // load service
        $taskBuffer = $this->get( 'task_buffer.task_buffer' );
    	
        // Queue invocation of \Application\SomeBundle\Entity\SomeObject::someStaticMethod();
        $taskBuffer->queue( '\Application\SomeBundle\Entity\SomeObject::someStaticMethod' );

        // Queue invocation of $someObject->someMethod();
        $someObject = new \Application\SomeBundle\Entity\SomeObject();
        $taskBuffer->queue( array( $someObject, 'someMethod' ) );

 		// Queue invocation of $objectX->hello22().
 		// Invocation will be executed in 2 hours window. For example, if queue is executed at 11.31 
 		// the window is from 10.31 to 12.31 every day till successfull execution of the task,
 		// or untill failuresLimit is reached what gives 5 tries. 
		// Task will not be executed if there are any tasks with higher priority awaiting, 
		// 'higher' means number lower than 50 (0 goes first, 1000 last).  
 		
 		$timeFrom = new \DateTime(date('H:i:s'));
        $timeUntil = new \DateTime(date('H:i:s'));
 		   
        $taskBuffer->
            failuresLimit(5)->
            priority(50)->
            time($timeFrom->modify('-1 houre'), $timeUntil->modify('+1 houre'))->
            queue( array( $objectX, 'someMethodOk2' ), 'hello22' );

        ...
    }

Pulling tasks from terminal:

    php app/console task-buffer:pull 10
    
'10' is default quantity of tasks, an may be ommited.    

TODO: more explanation!    
