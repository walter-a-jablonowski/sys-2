<?php

/**
 * AJAX handler - forwards calls to appropriate handlers
 */

header('Content-Type: application/json');

// Check if this is a file upload
if( !empty($_FILES) ) {
  handleFileUpload();
  exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if( !$input ) {
  echo json_encode(['success' => false, 'error' => 'Invalid input']);
  exit;
}

$action = $input['action'] ?? '';
$data = $input['data'] ?? [];

// Handle actions
try {
  switch( $action ) {
    case 'saveActivity':
      $result = saveActivity($data);
      break;
    
    case 'saveInfo':
      $result = saveInfo($data);
      break;
    
    case 'saveApartment':
      $result = saveApartment($data);
      break;
    
    case 'saveEntry':
      $result = saveEntry($data);
      break;
    
    case 'loadEditForm':
      $result = loadEditForm($data);
      break;
    
    case 'deleteEntry':
      $result = deleteEntry($data);
      break;
    
    case 'getNextFileNumber':
      $result = getNextFileNumber();
      break;
    
    default:
      throw new Exception('Unknown action: ' . $action);
  }
  
  echo json_encode(['success' => true, 'data' => $result]);
  
} catch( Exception $e ) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Function to save activity
function saveActivity($data) {
  // Implementation would save the activity data
  // For now, just return success
  return ['message' => 'Activity saved'];
}

// Function to save info
function saveInfo($data) {
  // Implementation would save the info data
  // For now, just return success
  return ['message' => 'Info saved'];
}

// Function to save apartment
function saveApartment($data) {
  // Implementation would save the apartment data
  // For now, just return success
  return ['message' => 'Apartment saved'];
}

// Function to save generic entry
function saveEntry($data) {
  // Implementation would save the entry data
  // For now, just return success
  return ['message' => 'Entry saved'];
}

// Function to load edit form
function loadEditForm($data) {
  $path = $data['path'] ?? '';
  
  if( empty($path) ) {
    throw new Exception('Path is required');
  }
  
  // In a real implementation, this would load the appropriate edit form
  // based on the entry type at the given path
  
  return ['html' => '<p>Edit form would be loaded here for: ' . htmlspecialchars($path) . '</p>'];
}

// Function to delete entry
function deleteEntry($data) {
  $path = $data['path'] ?? '';
  
  if( empty($path) ) {
    throw new Exception('Path is required');
  }
  
  // In a real implementation, this would delete the entry at the given path
  
  return ['message' => 'Entry deleted'];
}

// Function to get next file number for apartments
function getNextFileNumber() {
  $counterFile = 'user/Default/types/Apartment/files_nr.json';
  
  // Read current counter
  if( file_exists($counterFile) ) {
    $counterData = json_decode(file_get_contents($counterFile), true);
    $lastId = $counterData['lastId'] ?? 0;
  } else {
    $lastId = 0;
  }
  
  // Increment counter
  $nextId = $lastId + 1;
  
  // Save updated counter
  $counterData = ['lastId' => $nextId];
  file_put_contents($counterFile, json_encode($counterData));
  
  // Format as 4-digit string with leading zeros
  return ['fileNumber' => str_pad($nextId, 4, '0', STR_PAD_LEFT)];
}

// Function to handle file uploads
function handleFileUpload() {
  try {
    if( !isset($_FILES['image']) ) {
      throw new Exception('No image file provided');
    }
    
    $file = $_FILES['image'];
    $apartmentId = $_POST['apartmentId'] ?? '';
    
    if( empty($apartmentId) ) {
      throw new Exception('Apartment ID is required');
    }
    
    // Check for upload errors
    if( $file['error'] !== UPLOAD_ERR_OK ) {
      throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if( !in_array($file['type'], $allowedTypes) ) {
      throw new Exception('Invalid file type. Only JPEG, PNG, and GIF images are allowed.');
    }
    
    // Check file size (max 5MB)
    if( $file['size'] > 5 * 1024 * 1024 ) {
      throw new Exception('File too large. Maximum size is 5MB.');
    }
    
    // Create directory for apartment images if it doesn't exist
    $imagesDir = 'data/' . $apartmentId . '/images';
    if( !is_dir($imagesDir) ) {
      mkdir($imagesDir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $destination = $imagesDir . '/' . $filename;
    
    // Move uploaded file
    if( !move_uploaded_file($file['tmp_name'], $destination) ) {
      throw new Exception('Failed to move uploaded file');
    }
    
    echo json_encode(['success' => true, 'data' => ['message' => 'Image uploaded successfully']]);
    
  } catch( Exception $e ) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  }
}

?>
