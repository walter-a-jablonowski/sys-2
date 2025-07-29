<?php
require_once 'classes/TypeManager.php';
require_once 'classes/InstanceManager.php';

header('Content-Type: application/json');

try {
  $input = json_decode(file_get_contents('php://input'), true);
  $action = $input['action'] ?? '';

  $typeManager = new TypeManager();
  $instanceManager = new InstanceManager();

  switch( $action ) {
    case 'add_instance':
      $path = $input['path'] ?? 'data';
      $type = $input['type'] ?? '';
      $name = $input['name'] ?? '';
      $description = $input['description'] ?? '';

      if( empty($type) || empty($name) ) {
        throw new Exception('Type and name are required');
      }

      $data = [];
      if( ! empty($description) ) {
        $data['description'] = $description;
      }

      $success = $instanceManager->createInstance($path, $name, $type, $data);
      
      if( ! $success ) {
        throw new Exception('Failed to create instance');
      }

      echo json_encode(['success' => true]);
      break;

    case 'get_edit_form':
      $path = $input['path'] ?? '';
      
      if( empty($path) ) {
        throw new Exception('Path is required');
      }

      $instanceData = $instanceManager->loadInstance($path);
      if( ! $instanceData ) {
        throw new Exception('Instance not found');
      }

      $type = $instanceData['type'] ?? '';
      $editFile = "types/$type/edit.php";
      
      ob_start();
      if( file_exists($editFile) ) {
        include $editFile;
      } else {
        // Default edit form
        include 'default_edit.php';
      }
      $html = ob_get_clean();

      echo json_encode(['success' => true, 'html' => $html]);
      break;

    case 'save_instance':
      $path = $input['path'] ?? '';
      
      if( empty($path) ) {
        throw new Exception('Path is required');
      }

      // Remove action and path from input to get the data
      unset($input['action'], $input['path']);
      
      $success = $instanceManager->saveInstance($path, $input);
      
      if( ! $success ) {
        throw new Exception('Failed to save instance');
      }

      echo json_encode(['success' => true]);
      break;

    case 'delete_instance':
      $path = $input['path'] ?? '';
      
      if( empty($path) ) {
        throw new Exception('Path is required');
      }

      $success = $instanceManager->deleteInstance($path);
      
      if( ! $success ) {
        throw new Exception('Failed to delete instance');
      }

      echo json_encode(['success' => true]);
      break;

    case 'upload_image':
      // Special handler for apartment image uploads
      $apartmentPath = $input['apartment_path'] ?? '';
      
      if( empty($apartmentPath) ) {
        throw new Exception('Apartment path is required');
      }

      if( ! isset($_FILES['image']) ) {
        throw new Exception('No image file provided');
      }

      $file = $_FILES['image'];
      $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
      
      if( ! in_array($file['type'], $allowedTypes) ) {
        throw new Exception('Invalid file type. Only image files are allowed.');
      }

      $imagesDir = "$apartmentPath/images";
      if( ! is_dir($imagesDir) ) {
        mkdir($imagesDir, 0755, true);
      }

      $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
      $filename = date('YmdHis') . '.' . $extension;
      $targetPath = "$imagesDir/$filename";

      if( ! move_uploaded_file($file['tmp_name'], $targetPath) ) {
        throw new Exception('Failed to save image file');
      }

      echo json_encode(['success' => true, 'filename' => $filename]);
      break;

    default:
      // Forward to type-specific AJAX handlers
      if( preg_match('/^([a-zA-Z]+)_(.+)$/', $action, $matches) ) {
        $typeName = $matches[1];
        $typeAction = $matches[2];
        
        $ajaxFile = "types/$typeName/ajax/$typeAction.php";
        if( file_exists($ajaxFile) ) {
          include $ajaxFile;
        } else {
          throw new Exception("Action '$action' not found");
        }
      } else {
        throw new Exception("Unknown action: $action");
      }
      break;
  }

} catch( Exception $e ) {
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
