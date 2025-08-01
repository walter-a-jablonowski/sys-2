<?php

/**
 * AJAX handler - forwards calls to appropriate handlers
 */

// Include Composer autoloader
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

// Include utility functions
require_once 'lib/utils.php';

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
  // Use the generic save function
  return saveEntry($data);
}

// Function to save info
function saveInfo($data) {
  // Use the generic save function
  return saveEntry($data);
}

// Function to save apartment
function saveApartment($data) {
  // Use the generic save function
  return saveEntry($data);
}

// Function to save generic entry
function saveEntry($data) {
  $type = $data['type'] ?? '';
  $name = $data['name'] ?? '';
  $time = $data['time'] ?? '';
  $id = $data['id'] ?? '';
  
  if( empty($type) || empty($name) || empty($time) || empty($id) ) {
    throw new Exception('Missing required fields');
  }
  
  // Create directory if it doesn't exist
  $dir = 'data';
  if( !file_exists($dir) ) {
    mkdir($dir, 0777, true);
  }
  
  // Create filename
  $filename = $name . '/' . '-this.md';
  $filepath = $dir . '/' . $filename;
  
  // Create directory for the entry
  $entryDir = dirname($filepath);
  if( !file_exists($entryDir) ) {
    mkdir($entryDir, 0777, true);
  }
  
  // Prepare front matter
  $frontMatter = [
    'type' => $type,
    'name' => $name,
    'id' => $id,
    'time' => $time
  ];
  
  // Add other fields
  foreach( $data as $key => $value ) {
    if( !in_array($key, ['type', 'name', 'id', 'time']) && !empty($value) ) {
      $frontMatter[$key] = $value;
    }
  }
  
  // Convert to YAML
  $yaml = Yaml::dump($frontMatter);
  
  // Create content
  $content = "---\n" . $yaml . "\n---\n\n";
  
  // Save file
  if( !file_put_contents($filepath, $content) ) {
    throw new Exception('Failed to save entry');
  }
  
  return ['message' => 'Entry saved successfully'];
}

// Function to load edit form
function loadEditForm($data) {
  $path = $data['path'] ?? '';
  
  if( empty($path) ) {
    throw new Exception('Path is required');
  }
  
  // Load type definitions
  $types = loadTypeDefinitions();
  
  // Get item data
  $config = ['dataFileName' => '-this'];
  $item = getItemAtPath($path, $config['dataFileName'], $types);
  
  if( !$item ) {
    throw new Exception('Item not found');
  }
  
  $type = $item['type'];
  $itemData = $item['data'];
  
  // Check if type has edit renderer
  $editFile = "types/{$type}/edit.php";
  if( !file_exists($editFile) ) {
    throw new Exception('Edit form not available for this type');
  }
  
  // Start output buffering
  ob_start();
  
  // Include the edit form
  include $editFile;
  
  // Get the content
  $html = ob_get_clean();
  
  return ['html' => $html];
}

// Function to delete entry
function deleteEntry($data) {
  $path = $data['path'] ?? '';
  
  if( empty($path) ) {
    throw new Exception('Path is required');
  }
  
  // Full path to the entry
  $fullPath = 'data/' . $path;
  
  if( !file_exists($fullPath) ) {
    throw new Exception('Entry not found');
  }
  
  // Check if it's a file or directory
  if( is_file($fullPath) ) {
    // Delete the file
    if( !unlink($fullPath) ) {
      throw new Exception('Failed to delete entry');
    }
  } elseif( is_dir($fullPath) ) {
    // Delete the directory and all its contents
    deleteDirectory($fullPath);
  }
  
  return ['message' => 'Entry deleted successfully'];
}

// Helper function to delete a directory recursively
function deleteDirectory($dir) {
  if( !file_exists($dir) ) {
    return true;
  }
  
  if( !is_dir($dir) ) {
    return unlink($dir);
  }
  
  foreach( scandir($dir) as $item ) {
    if( $item == '.' || $item == '..' ) {
      continue;
    }
    
    if( !deleteDirectory($dir . DIRECTORY_SEPARATOR . $item) ) {
      return false;
    }
  }
  
  return rmdir($dir);
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
