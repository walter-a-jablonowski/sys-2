<?php
/**
 * Handle saving an entry - common for all types
 */
function handleSaveEntry( $data, $typeManager, $dataManager )
{
  if( !isset($data['path']) ) {
    throw new Exception('Missing required field: path');
  }
  
  $path = $data['path'];
  $instance = $dataManager->getInstance($path);
  
  if( !$instance ) {
    throw new Exception('Entry not found');
  }
  
  // Update instance with new data
  $dataManager->updateEntry($path, $data);
  
  return ['success' => true];
}
?>
