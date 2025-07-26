<?php
// Ajax request handler - forwards calls to specific ajax functions

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: application/json; charset=UTF-8');

// Include core functions
require_once 'core/functions.php';
require_once 'core/type_manager.php';
require_once 'core/data_manager.php';

try {
  // Get POST data
  $input = json_decode(file_get_contents('php://input'), true);
  
  if( !$input || !isset($input['action']) ) {
    throw new Exception('Invalid request format');
  }
  
  $action = $input['action'];
  $data = $input['data'] ?? [];
  
  // Initialize managers
  $typeManager = new TypeManager();
  $dataManager = new DataManager();
  
  // Route to appropriate handler
  switch( $action ) {
    case 'add_entry':
      require_once 'ajax/add_entry.php';
      $result = handleAddEntry($data, $typeManager, $dataManager);
      break;
      
    case 'edit_entry':
      require_once 'ajax/edit_entry.php';
      $result = handleEditEntry($data, $typeManager, $dataManager);
      break;
      
    case 'save_entry':
      require_once 'ajax/save_entry.php';
      $result = handleSaveEntry($data, $typeManager, $dataManager);
      break;
      
    case 'delete_entry':
      require_once 'ajax/delete_entry.php';
      $result = handleDeleteEntry($data, $dataManager);
      break;
      
    case 'upload_image':
      // Type-specific handler for Apartment
      require_once 'types/Apartment/ajax/upload_image.php';
      $result = handleUploadImage($data, $dataManager);
      break;
      
    case 'get_list':
      require_once 'ajax/get_list.php';
      $result = handleGetList($data, $dataManager);
      break;
      
    default:
      throw new Exception('Unknown action: ' . $action);
  }
  
  echo json_encode(['success' => true, 'data' => $result]);
  
} catch( Exception $e ) {
  http_response_code(400);
  echo json_encode([
    'success' => false, 
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
  ]);
}


?>
