<?php
require_once 'lib/DataManager.php';

use Symfony\Component\Yaml\Yaml;

try 
{
  $path = $_POST['path'] ?? '';
  
  if( empty($path) )
    throw new Exception('Path is required');
  
  $dataManager = new DataManager();
  
  // Load the entry data
  $fullPath = 'data/' . $path;
  
  if( is_dir($fullPath) )
  {
    $config = Yaml::parseFile('config.yml');
    $dataFile = $fullPath . '/' . $config['dataFileName'] . '.md';
  }
  else
  {
    $dataFile = $fullPath;
  }
  
  if( ! file_exists($dataFile) )
    throw new Exception("Entry not found: $path");
  
  $entry = $dataManager->parseDataFile($dataFile);
  $entry['_path'] = $path;
  
  $type = $entry['type'] ?? 'Info';
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
  throw new Exception('Failed to load edit form: ' . $e->getMessage());
}
