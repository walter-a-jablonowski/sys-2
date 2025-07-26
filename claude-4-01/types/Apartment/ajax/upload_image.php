<?php
/**
 * Handle image upload for Apartment type (type-specific)
 */
function handleUploadImage( $data, $dataManager )
{
  if( !isset($data['path']) || !isset($_FILES['image']) ) {
    throw new Exception('Missing required fields: path or image file');
  }
  
  $path = $data['path'];
  $uploadedFile = $_FILES['image'];
  
  // Validate file type (smartphone camera images)
  $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/heic', 'image/heif'];
  if( !in_array($uploadedFile['type'], $allowedTypes) ) {
    throw new Exception('Invalid file type. Only JPEG, PNG, HEIC, and HEIF images are allowed.');
  }
  
  // Validate file size (max 10MB)
  $maxSize = 10 * 1024 * 1024; // 10MB
  if( $uploadedFile['size'] > $maxSize ) {
    throw new Exception('File too large. Maximum size is 10MB.');
  }
  
  // Upload the image
  $fileName = $dataManager->uploadImage($path, $uploadedFile);
  
  return ['fileName' => $fileName, 'success' => true];
}
?>
