<?php
/**
 * Handle adding a new entry (common for all types)
 */
function handleAddEntry( $data, $typeManager, $dataManager )
{
  if( !isset($data['type'], $data['name'], $data['path']) ) {
    throw new Exception('Missing required fields: type, name, path');
  }
  
  $type = $data['type'];
  $name = $data['name'];
  $description = $data['description'] ?? '';
  $path = $data['path'];
  
  // Validate type
  if( !$typeManager->isValidType($type) ) {
    throw new Exception('Invalid type: ' . $type);
  }
  
  // Create the entry
  $entryId = $dataManager->createEntry($type, $name, $description, $path);
  
  return ['entryId' => $entryId];
}
?>
