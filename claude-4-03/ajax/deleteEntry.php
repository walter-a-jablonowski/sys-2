<?php

require_once 'lib/DataManager.php';

$path = $input['path'] ?? '';

if( empty($path) )
  throw new Exception('Path is required');
  
DataManager::deleteEntry($path);

echo json_encode(['success' => true, 'message' => 'Entry deleted successfully']);
?>
