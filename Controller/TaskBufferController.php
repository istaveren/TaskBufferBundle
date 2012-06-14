<?php

namespace Smentek\TaskBufferBundle\Controller;

use Symfony\Component\Console\Output\StreamOutput;

use n3b\Bundle\Util\HttpFoundation\StreamResponse\FileWriter;

use n3b\Bundle\Util\HttpFoundation\StreamResponse\StreamResponse;

use Gaufrette\StreamWrapper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Smentek\TaskBufferBundle\Entity\TaskBuffer;
use Smentek\TaskBufferBundle\Entity\Task;

class TaskBufferController extends Controller
{

    public function taskGroupsAction( $offset = 0, $limit = 10 )
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $queryGroupsOnPage = $em->createQuery( "SELECT tg FROM \Smentek\TaskBufferBundle\Entity\TaskGroup tg WHERE tg.isActive = true" );
        //        	->setFirstResult( $offset )
        //    		->setMaxResults( $limit );
        $taskGroups = $queryGroupsOnPage->getResult();

        $queryAllGroupsQuantity = $em->createQuery( "SELECT COUNT( tg ) FROM \Smentek\TaskBufferBundle\Entity\TaskGroup tg WHERE tg.isActive = true" );
        $quantityOfTaskGroups = $queryAllGroupsQuantity->getSingleScalarResult();

        $statusSuccess = Task::STATUS_SUCCESS;
        $statusAwaiting = Task::STATUS_AWAITING;
        $statusInvalidCallback = Task::STATUS_INVALID_CALLABACK;
        $statusRuntimeException = Task::STATUS_RUNTIME_EXCEPTION;

        $statusSuccesQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusSuccess}" );
        $statusSuccessQuantity = $statusSuccesQuantityQuery->getSingleScalarResult();

        $statusAwaitingQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusAwaiting}" );
        $statusAwaitingQuantity = $statusAwaitingQuantityQuery->getSingleScalarResult();

        $statusInvalidCallbackQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusInvalidCallback}" );
        $statusInvalidCallbackQuantity = $statusInvalidCallbackQuantityQuery->getSingleScalarResult();

        $statusRuntimeExceptionQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t WHERE t.status = {$statusRuntimeException}" );
        $statusRuntimeExceptionQuantity = $statusRuntimeExceptionQuantityQuery->getSingleScalarResult();

        $form = $this->createDateForm();
        
        $viewResult = array(
		    'statusSuccessQuantity' => $statusSuccessQuantity,
		    'statusAwaitingQuantity' => $statusAwaitingQuantity,
		    'statusInvalidCallbackQuantity' => $statusInvalidCallbackQuantity,
		    'statusRuntimeExceptionQuantity' => $statusRuntimeExceptionQuantity,
		    'taskGroups' => $taskGroups,
            'quantity_of_task_groups' => $quantityOfTaskGroups,
            'form' => $form->createView(),
        );

        return $this->render('TaskBufferBundle:TaskBuffer:task_groups.html.twig', $viewResult);
    }

    /**
     * Create date form
     * @return Form
     */
    protected function createDateForm()
    {
      return $this->createFormBuilder(array('fromDate' => new \DateTime('-7 days')))
        ->add('fromDate', 'date')
        ->getForm();
    }
    
    public function taskGroupAction( $taskGroupId )
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $taskGroup = $em->find('\Smentek\TaskBufferBundle\Entity\TaskGroup', $taskGroupId);


        $statusSuccess = Task::STATUS_SUCCESS;
        $statusAwaiting = Task::STATUS_AWAITING;
        $statusInvalidCallback = Task::STATUS_INVALID_CALLABACK;
        $statusRuntimeException = Task::STATUS_RUNTIME_EXCEPTION;

        $statusSuccesQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t JOIN t.taskGroup tg WHERE t.status = {$statusSuccess} AND tg.taskGroupId = {$taskGroupId}" );
        $statusSuccessQuantity = $statusSuccesQuantityQuery->getSingleScalarResult();

        $statusAwaitingQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t JOIN t.taskGroup tg WHERE t.status = {$statusAwaiting} AND tg.taskGroupId = {$taskGroupId}" );
        $statusAwaitingQuantity = $statusAwaitingQuantityQuery->getSingleScalarResult();

        $statusInvalidCallbackQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t JOIN t.taskGroup tg WHERE t.status = {$statusInvalidCallback} AND tg.taskGroupId = {$taskGroupId}" );
        $statusInvalidCallbackQuantity = $statusInvalidCallbackQuantityQuery->getSingleScalarResult();

        $statusRuntimeExceptionQuantityQuery = $em->createQuery( "SELECT COUNT(t.taskId) as success_quantity FROM \Smentek\TaskBufferBundle\Entity\Task t JOIN t.taskGroup tg WHERE t.status = {$statusRuntimeException} AND tg.taskGroupId = {$taskGroupId}" );
        $statusRuntimeExceptionQuantity = $statusRuntimeExceptionQuantityQuery->getSingleScalarResult();

        $viewResult = array(
        	'taskGroup' => $taskGroup, 
		    'statusSuccessQuantity' => $statusSuccessQuantity,
		    'statusAwaitingQuantity' => $statusAwaitingQuantity,
		    'statusInvalidCallbackQuantity' => $statusInvalidCallbackQuantity,
		    'statusRuntimeExceptionQuantity' => $statusRuntimeExceptionQuantity,
        );
        return $this->render('TaskBufferBundle:TaskBuffer:task_group.html.twig', $viewResult);
    }

    public function taskDeleteAction( $taskId )
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $task = $em->find('\Smentek\TaskBufferBundle\Entity\Task', $taskId);

        $taskGroup = $task->getTaskGroup();
         
        $em->remove($task);
        $em->flush();

        return $this->redirect($this->generateUrl('task_group', array('taskGroupId' => $taskGroup->getTaskGroupId())));
    }

    public function taskGroupDeleteAction( $taskGroupId )
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $taskGroup = $em->find('\Smentek\TaskBufferBundle\Entity\TaskGroup', $taskGroupId);

        $em->remove($taskGroup);
        $em->flush();

        return $this->redirect($this->generateUrl('task_groups'));
    }
    
    /**
     * Delete old sucesfull tasks
     */
    public function deleteOldTasksAction()
    {
      $taskBuffer = $this->container->get('task_buffer');
      $form = $this->createDateForm();
      if ($this->getRequest()->getMethod() == 'POST')
      {
        $form->bindRequest($this->getRequest());
        
        if ($form->isValid())
        {
          // perform some action, such as saving the task to the database
          $dateobj = $form->get('fromDate');
          $taskBuffer->clean($dateobj->getData());
          $this->get('session')->setFlash('notice', 'Records are cleaned!');
        }
        else
        {
          $this->get('session')->setFlash('notice', 'Form invalid!');
        }
      }
      
      return $this->redirect($this->generateUrl('task_groups'));
    }
    
    public function pullAction($limit = 10, $stop = false)
    {
      $stream = new StreamOutput(tmpfile());
      
      // Get instance of matchengine
      $this->container->get('projectx.match_engine');
      $taskBuffer = $this->container->get('task_buffer');
      $taskBuffer->setContainer($this->container);
      $taskBuffer->setOutput($stream);
      $taskBuffer->setPullLimit($limit);
      $taskBuffer->pull($stop);
      
      $output = fread($stream->getStream(), 3000);
      
      return $this->render('TaskBufferBundle:TaskBuffer:pull.html.twig', array('output' => $output));
    }
}