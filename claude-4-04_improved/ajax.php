<?php
require_once 'vendor/autoload.php';

header('Content-Type: application/json');

try 
{
  // Get the function name from the request
  $function = $_POST['function'] ?? $_GET['function'] ?? '';
  
  if( empty($function) )
    throw new Exception('No function specified');
  
  // Check for type-specific AJAX handler first
  $type = $_POST['type'] ?? $_GET['type'] ?? '';
  if( $type && file_exists("types/$type/ajax/$function.php") )
  {
    include "types/$type/ajax/$function.php";
  }
  // Check for global AJAX handler
  elseif( file_exists("ajax/$function.php") )
  {
    include "ajax/$function.php";
  }
  else
  {
    throw new Exception("Function '$function' not found");
  }
}
catch( Exception $e )
{
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}
