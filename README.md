Provides simple queuing for Symfony2 projects.

Work in progress... Bundle not stable yet!

Installation
============

For: https://github.com/symfony/symfony-sandbox rev: 59879d952e7a7847e9ed. 

  1. Add this bundle to your src/ dir

        $ git submodule add git://github.com/smentek/TaskBufferBundle.git src/Smentek/TaskBufferBundle

  2. Add this bundle to your application's kernel (app/AppKernel.php):

        public function registerBundles()
        {
            //...
            new Smentek\TaskBufferBundle\TaskBufferBundle(),
            //...
        }

  3. Configure the routing (app/config/routing.yml):
        
        task_buffer:
          resource: @TaskBufferBundle/Resources/config/routing.yml

  4. Configure the service in your config (config/app/config.yml):

        ## Doctrine Configuration
        doctrine.dbal:
          dbname:   symfony-sandbox
          user:     dbuser
          password: dbpass
        doctrine.orm:
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

How to use
----------

In the controller we have some action. In this action we queue static method and normal method call.

    public function someAction()
    {
        ...

        // load service
        $taskBuffer = $this->get( 'task_buffer.task_buffer' );
    	
        // queue static method call 
        $taskBuffer->queue( '\Application\SomeBundle\Entity\SomeObject::someStaticMethod' );

        // queue object method method call    	
        $someObject = new \Application\SomeBundle\Entity\SomeObject();
        $taskBuffer->queue( array( $someObject, 'someMethod' ) );

        ...
    }

CRON or similar system should pull tasks: 

    php app/console task-buffer:pull 10
    
Where '10' is number of tasks, an may be ommited.    

TODO: more explanation!    