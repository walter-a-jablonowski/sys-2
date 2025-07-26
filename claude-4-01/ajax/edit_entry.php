<?php
/**
 * Handle editing an entry (get edit form) - common for all types
 */
function handleEditEntry( $data, $typeManager, $dataManager )
{
  if( !isset($data['path']) ) {
    throw new Exception('Missing required field: path');
  }
  
  $path = $data['path'];
  $instance = $dataManager->getInstance($path);
  
  if( !$instance ) {
    throw new Exception('Entry not found');
  }
  
  $editForm = $dataManager->renderEditForm($instance);
  
  return ['html' => $editForm];
}
?>
