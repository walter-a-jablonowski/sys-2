<?php
/**
 * Handle deleting an entry - common for all types
 */
function handleDeleteEntry( $data, $dataManager )
{
  if( !isset($data['path']) ) {
    throw new Exception('Missing required field: path');
  }
  
  $path = $data['path'];
  
  // Delete the entry
  $dataManager->deleteEntry($path);
  
  return ['success' => true];
}
?>
