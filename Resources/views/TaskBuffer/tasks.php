<?php echo 'tasks.php'; ?>

<?php //var_dump( $taskGroups ); 
?>

<?php if( count( $taskGroups ) ): ?>
	<div>
	<?php foreach( $taskGroups as $taskGroup ): ?>
		<div>
		<div><?php echo $taskGroup->getIdentifier();?></div>
		<div><?php echo $taskGroup->getPriority();?></div>
		
		<?php if( $taskGroup->getStartTime() ):?>
			<div><?php echo $taskGroup->getStartTime(); ?></div>
		<?php endif;?>
		
		<?php if( $taskGroup->getEndTime() ):?>
			<div><?php echo $taskGroup->getEndTime(); ?></div>
		<?php endif;?>
		
		<div><?php echo $taskGroup->getFailuresLimit();?></div>
		
		<div>
		<?php if( count( $taskGroup->getTasks() ) ): ?>
			<?php foreach( $taskGroup->getTasks() as $task ): ?>
			
				<div><?php echo $task->getCreatedAt()->format('Y-m-d H:i:s'); ?></div>
				<div><?php echo $task->getFailuresCount(); ?></div>
			
			<?php endforeach; ?>
		<?php endif;?>
		</div>
		</div>
	<?php endforeach; ?>
	</div>
<?php endif;?>