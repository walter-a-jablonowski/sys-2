<?php

require_once 'lib/Config.php';
require_once 'lib/TypeManager.php';
require_once 'lib/DataManager.php';

$path = $input['path'] ?? '';
$data = $input['data'] ?? [];
$entryType = $input['entryType'] ?? '';

if( empty($path) )
  throw new Exception('Path is required');

// Handle priority-based naming for Activity and Apartment types
$newPath = $path;
if( ($entryType === 'Activity' || $entryType === 'Apartment') && isset($data['priority']) && isset($data['name']) )
{
  $currentEntry = DataManager::loadEntry($path);
  $currentPriority = $currentEntry['priority'] ?? 3;
  $newPriority = $data['priority'];
  
  // Check if priority changed and update folder/file name accordingly
  if( $currentPriority != $newPriority )
  {
    $parentDir = dirname($path);
    $currentName = basename($path);
    
    // Extract the name part (remove current priority prefix)
    $namePart = preg_replace('/^\d+\s*-\s*/', '', $currentName);
    $newName = "$newPriority - $namePart";
    $newPath = "$parentDir/$newName";
    
    // Rename the directory/file
    if( $path !== $newPath )
    {
      if( ! rename($path, $newPath) )
        throw new Exception('Failed to rename entry with new priority');
    }
  }
}

// Add type and time to data if not present
if( ! isset($data['type']) && $entryType )
  $data['type'] = $entryType;
  
if( ! isset($data['time']) )
{
  $currentEntry = DataManager::loadEntry($newPath);
  $data['time'] = $currentEntry['time'] ?? date('Y-m-d H:i:s');
}

DataManager::saveEntry($newPath, $data);

echo json_encode(['success' => true, 'message' => 'Entry updated successfully', 'newPath' => $newPath]);
?>
