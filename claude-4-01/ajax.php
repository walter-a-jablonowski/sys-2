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
      $result = handleAddEntry($data, $typeManager, $dataManager);
      break;
      
    case 'edit_entry':
      $result = handleEditEntry($data, $typeManager, $dataManager);
      break;
      
    case 'save_entry':
      $result = handleSaveEntry($data, $typeManager, $dataManager);
      break;
      
    case 'delete_entry':
      $result = handleDeleteEntry($data, $dataManager);
      break;
      
    case 'upload_image':
      $result = handleUploadImage($data, $dataManager);
      break;
      
    case 'get_list':
      $result = handleGetList($data, $dataManager);
      break;
      
    default:
      throw new Exception("Unknown action: $action");
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

/**
 * Handle adding a new entry
 */
function handleAddEntry( $data, $typeManager, $dataManager )
{
  if( !isset($data['type'], $data['name'], $data['path']) ) {
    throw new Exception('Missing required fields: type, name, path');
  }
  
  $type = $data['type'];
  $name = $data['name'];
  $description = $data['description'] ?? '';
  $path = $data['path'];
  
  // Validate type exists and is allowed
  if( !$typeManager->typeExists($type) ) {
    throw new Exception("Type '$type' does not exist");
  }
  
  if( !$typeManager->isTypeAllowed($type, $path) ) {
    throw new Exception("Type '$type' is not allowed at this level");
  }
  
  // Create the entry
  $entryId = $dataManager->createEntry($type, $name, $description, $path);
  
  return ['entryId' => $entryId];
}

/**
 * Handle editing an entry (get edit form)
 */
function handleEditEntry( $data, $typeManager, $dataManager )
{
  if( !isset($data['path']) ) {
    throw new Exception('Missing required field: path');
  }
  
  $path = $data['path'];
  $instance = $dataManager->getInstance($path);
  
  if( !$instance ) {
    throw new Exception('Entry not found');
  }
  
  $editForm = $dataManager->renderEditForm($instance);
  
  return ['html' => $editForm];
}

/**
 * Handle saving an entry
 */
function handleSaveEntry( $data, $typeManager, $dataManager )
{
  if( !isset($data['path'], $data['fields']) ) {
    throw new Exception('Missing required fields: path, fields');
  }
  
  $path = $data['path'];
  $fields = $data['fields'];
  
  // Validate and save
  $dataManager->saveEntry($path, $fields);
  
  return ['message' => 'Entry saved successfully'];
}

/**
 * Handle deleting an entry
 */
function handleDeleteEntry( $data, $dataManager )
{
  if( !isset($data['path']) ) {
    throw new Exception('Missing required field: path');
  }
  
  $path = $data['path'];
  $dataManager->deleteEntry($path);
  
  return ['message' => 'Entry deleted successfully'];
}

/**
 * Handle image upload for Apartment type
 */
function handleUploadImage( $data, $dataManager )
{
  if( !isset($_FILES['image'], $data['path']) ) {
    throw new Exception('Missing image file or path');
  }
  
  $path = $data['path'];
  $file = $_FILES['image'];
  
  // Validate file type (hardcoded image types)
  $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
  
  if( !in_array($file['type'], $allowedTypes) ) {
    throw new Exception('Invalid file type. Only image files are allowed.');
  }
  
  // Save the image
  $filename = $dataManager->saveImage($file, $path);
  
  return ['filename' => $filename];
}

/**
 * Handle getting list data
 */
function handleGetList( $data, $dataManager )
{
  $path = $data['path'] ?? '';
  $sort = $data['sort'] ?? 'time';
  
  $listData = $dataManager->getCurrentLevelData($path);
  $listData['entries'] = $dataManager->sortEntries($listData['entries'] ?? [], $sort);
  
  $html = $dataManager->renderList($listData['entries']);
  
  return ['html' => $html];
}
?>
