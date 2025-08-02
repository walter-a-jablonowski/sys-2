<?php
require_once 'lib/DataManager.php';

try 
{
  $path = $_POST['path'] ?? '';
  
  $dataManager = new DataManager();
  $types = $dataManager->getAvailableTypes($path);
  
  echo json_encode([
    'success' => true,
    'types' => $types
  ]);
}
catch( Exception $e )
{
  throw new Exception('Failed to load available types: ' . $e->getMessage());
}
