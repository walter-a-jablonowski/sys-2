<?php
require_once 'lib/DataManager.php';

try 
{
  $path = $_POST['path'] ?? '';
  
  if( empty($path) )
    throw new Exception('Path is required');
  
  $dataManager = new DataManager();
  $dataManager->deleteEntry($path);
  
  echo json_encode([
    'success' => true,
    'message' => 'Entry deleted successfully'
  ]);
}
catch( Exception $e )
{
  throw new Exception('Failed to delete entry: ' . $e->getMessage());
}
