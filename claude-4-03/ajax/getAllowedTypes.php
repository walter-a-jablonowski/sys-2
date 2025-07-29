<?php

require_once 'lib/TypeManager.php';
require_once 'lib/DataManager.php';

$currentPath = $input['path'] ?? 'data';
$currentEntry = null;

if( $currentPath !== 'data' )
{
  $currentEntry = DataManager::loadEntry($currentPath);
}

$allowedTypes = [];
$allTypes = TypeManager::getAllTypes();

if( $currentEntry && isset($currentEntry['type']) )
{
  $currentType = TypeManager::getType($currentEntry['type']);
  $allowedSubTypes = $currentType['allowedSubTypes'] ?? [];
  
  if( in_array('*', $allowedSubTypes) )
  {
    $allowedTypes = $allTypes;
  }
  else
  {
    foreach( $allowedSubTypes as $typeId )
    {
      if( isset($allTypes[$typeId]) )
        $allowedTypes[$typeId] = $allTypes[$typeId];
    }
  }
}
else
{
  // At root level, allow all types
  $allowedTypes = $allTypes;
}

echo json_encode(['success' => true, 'data' => $allowedTypes]);
?>
