<?php
require_once 'lib/DataManager.php';

try 
{
  $type = $_POST['type'] ?? '';
  $path = $_POST['path'] ?? '';
  
  if( empty($type) )
    throw new Exception('Type is required');
  
  $dataManager = new DataManager();
  $typeRenderer = $dataManager->getTypeRenderer($type, 'edit');
  
  if( ! $typeRenderer )
    throw new Exception("Edit form not found for type: $type");
  
  // Start output buffering to capture the form HTML
  ob_start();
  include $typeRenderer;
  $html = ob_get_clean();
  
  echo json_encode([
    'success' => true,
    'html' => $html,
    'type' => $type
  ]);
}
catch( Exception $e )
{
  throw new Exception('Failed to load add form: ' . $e->getMessage());
}
