<?php

require_once 'lib/DataManager.php';

$path = $input['path'] ?? '';

if( empty($path) )
  throw new Exception('Path is required');
  
$entry = DataManager::loadEntry($path);
echo json_encode(['success' => true, 'data' => $entry]);
?>
