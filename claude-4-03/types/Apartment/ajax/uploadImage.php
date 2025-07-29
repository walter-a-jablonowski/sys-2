<?php

header('Content-Type: application/json');

try {
  if( ! isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK )
    throw new Exception('No image uploaded or upload error');
    
  $apartmentPath = $_POST['apartmentPath'] ?? '';
  if( empty($apartmentPath) )
    throw new Exception('Apartment path is required');
    
  // Create images directory if it doesn't exist
  $imagesDir = "$apartmentPath/images";
  if( ! is_dir($imagesDir) )
    mkdir($imagesDir, 0755, true);
    
  // Validate file type
  $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
  $fileType = $_FILES['image']['type'];
  
  if( ! in_array($fileType, $allowedTypes) )
    throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
    
  // Generate unique filename
  $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
  $filename = date('Ymd_His') . '_' . uniqid() . '.' . $extension;
  $targetPath = "$imagesDir/$filename";
  
  // Move uploaded file
  if( ! move_uploaded_file($_FILES['image']['tmp_name'], $targetPath) )
    throw new Exception('Failed to save uploaded image');
    
  echo json_encode([
    'success' => true, 
    'message' => 'Image uploaded successfully',
    'filename' => $filename,
    'path' => $targetPath
  ]);
}
catch( Exception $e ) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
