<?php
require_once 'lib/DataManager.php';

try 
{
  $dataManager = new DataManager();
  
  // Get form data
  $data = [];
  foreach( $_POST as $key => $value )
  {
    if( $key !== 'function' && $key !== 'type' && $key !== 'path' )
    {
      $data[$key] = $value;
    }
  }
  
  $type = $_POST['type'] ?? '';
  $path = $_POST['path'] ?? '';
  
  if( empty($type) )
    throw new Exception('Type is required');
  
  // Set the type in data
  $data['type'] = $type;
  
  // Handle special logic for different types
  if( $type === 'Apartment' )
  {
    // Handle image uploads
    if( isset($_FILES['apartmentImage']) && $_FILES['apartmentImage']['error'] === UPLOAD_ERR_OK )
    {
      $uploadResult = handleApartmentImageUpload($_FILES['apartmentImage'], $data, $path);
      if( ! $uploadResult )
        throw new Exception('Failed to upload apartment image');
    }
    
    // Ensure files_nr is properly formatted
    if( isset($data['files_nr']) )
    {
      $data['files_nr'] = str_pad($data['files_nr'], 4, '0', STR_PAD_LEFT);
    }
  }
  
  // Validate required fields based on type definition
  $typeDef = $dataManager->getTypeDefinition($type);
  if( $typeDef && isset($typeDef['fields']) )
  {
    foreach( $typeDef['fields'] as $fieldName => $fieldDef )
    {
      if( isset($fieldDef['required']) && $fieldDef['required'] )
      {
        if( empty($data[$fieldName]) )
          throw new Exception("Field '$fieldName' is required");
      }
      
      // Validate format if specified
      if( isset($fieldDef['format']) && ! empty($data[$fieldName]) )
      {
        if( ! preg_match('/' . $fieldDef['format'] . '/', $data[$fieldName]) )
          throw new Exception("Field '$fieldName' format is invalid");
      }
      
      // Validate numeric ranges
      if( isset($fieldDef['type']) && $fieldDef['type'] === 'int' && ! empty($data[$fieldName]) )
      {
        $value = intval($data[$fieldName]);
        if( isset($fieldDef['min']) && $value < $fieldDef['min'] )
          throw new Exception("Field '$fieldName' must be at least {$fieldDef['min']}");
        if( isset($fieldDef['max']) && $value > $fieldDef['max'] )
          throw new Exception("Field '$fieldName' must be at most {$fieldDef['max']}");
      }
    }
  }
  
  // Save the entry
  $result = $dataManager->saveEntry($data, $path);
  
  echo json_encode([
    'success' => true,
    'message' => 'Entry saved successfully',
    'file' => $result
  ]);
}
catch( Exception $e )
{
  throw new Exception('Failed to save entry: ' . $e->getMessage());
}

function handleApartmentImageUpload( $file, $data, $path )
{
  $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
  
  if( ! in_array($file['type'], $allowedTypes) )
    throw new Exception('Invalid image type. Allowed: JPG, PNG, GIF, WebP');
  
  // Create images directory in the apartment folder
  $basePath = empty($path) ? 'data' : 'data/' . $path;
  $apartmentName = $data['name'] ?? 'apartment';
  
  // Generate folder name based on apartment entry
  $folderName = '';
  if( isset($data['priority']) )
  {
    $folderName = $data['priority'] . ' - ' . $apartmentName;
  }
  else
  {
    $folderName = $apartmentName;
  }
  
  $apartmentPath = $basePath . '/' . $folderName;
  $imagesPath = $apartmentPath . '/images';
  
  if( ! is_dir($imagesPath) )
    mkdir($imagesPath, 0755, true);
  
  // Generate unique filename
  $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
  $filename = 'apartment_' . date('YmdHis') . '.' . $extension;
  $targetPath = $imagesPath . '/' . $filename;
  
  if( move_uploaded_file($file['tmp_name'], $targetPath) )
    return true;
  
  return false;
}
