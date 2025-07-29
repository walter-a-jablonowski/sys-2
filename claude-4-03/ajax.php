<?php

header('Content-Type: application/json');

try {
  $input = json_decode(file_get_contents('php://input'), true);
  $action = $input['action'] ?? '';
  
  // Check for common AJAX functions first
  $commonActions = ['getEntries', 'getResources', 'createEntry', 'updateEntry', 'deleteEntry', 'getEntry', 'getEntryRenderer', 'getAllowedTypes'];
  
  if( in_array($action, $commonActions) )
  {
    $ajaxFile = "ajax/$action.php";
    if( file_exists($ajaxFile) )
    {
      include $ajaxFile;
      exit;
    }
  }
  
  // Check for type-specific ajax handlers
  $typeAction = explode('_', $action, 2);
  if( count($typeAction) === 2 )
  {
    $type = $typeAction[0];
    $typeAction = $typeAction[1];
    $handlerFile = "types/$type/ajax/$typeAction.php";
    
    if( file_exists($handlerFile) )
    {
      include $handlerFile;
      exit;
    }
  }
  
  throw new Exception("Unknown action: $action");
}
catch( Exception $e ) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
