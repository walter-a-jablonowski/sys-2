<?php

header('Content-Type: application/json');

try {
  require_once 'lib/Config.php';
  require_once 'lib/TypeManager.php';
  require_once 'lib/DataManager.php';

  $input = json_decode(file_get_contents('php://input'), true);
  $action = $input['action'] ?? '';
  
  switch( $action )
  {
    case 'getEntries':
      $path = $input['path'] ?? 'data';
      $entries = DataManager::getEntries($path);
      echo json_encode(['success' => true, 'data' => $entries]);
      break;
      
    case 'getResources':
      $path = $input['path'] ?? 'data';
      $resources = DataManager::getResources($path);
      echo json_encode(['success' => true, 'data' => $resources]);
      break;
      
    case 'getTypes':
      $types = TypeManager::getAllTypes();
      echo json_encode(['success' => true, 'data' => $types]);
      break;
      
    case 'createEntry':
      $name = $input['name'] ?? '';
      $type = $input['type'] ?? '';
      $description = $input['description'] ?? '';
      $path = $input['path'] ?? 'data';
      
      if( empty($name) || empty($type) )
        throw new Exception('Name and type are required');
        
      // Generate ID and create entry data
      $id = TypeManager::generateId($name);
      $time = date('Y-m-d H:i:s');
      
      $data = [
        'id' => $id,
        'time' => $time,
        'name' => $name,
        'type' => $type,
        'description' => $description
      ];
      
      // Handle special fields for Apartment type
      if( $type === 'Apartment' )
      {
        $data['files_nr'] = self::getNextFilesNr();
        $data['state'] = 'new';
      }
      elseif( $type === 'Activity' )
      {
        $data['priority'] = 3;
        $data['state'] = 'new';
      }
      
      // Create entry path based on type identification
      $typeDef = TypeManager::getType($type);
      if( $type === 'Activity' || $type === 'Apartment' )
      {
        $priority = $data['priority'] ?? 3;
        $entryName = "$priority - $name";
      }
      elseif( $type === 'Info' )
      {
        $entryName = "$name (i)";
      }
      else
      {
        $entryName = $name;
      }
      
      $entryPath = "$path/$entryName";
      
      // Create directory and save data
      if( ! is_dir($entryPath) )
        mkdir($entryPath, 0755, true);
        
      DataManager::saveEntry($entryPath, $data);
      
      echo json_encode(['success' => true, 'message' => 'Entry created successfully']);
      break;
      
    case 'updateEntry':
      $path = $input['path'] ?? '';
      $data = $input['data'] ?? [];
      
      if( empty($path) )
        throw new Exception('Path is required');
        
      DataManager::saveEntry($path, $data);
      
      echo json_encode(['success' => true, 'message' => 'Entry updated successfully']);
      break;
      
    case 'deleteEntry':
      $path = $input['path'] ?? '';
      
      if( empty($path) )
        throw new Exception('Path is required');
        
      DataManager::deleteEntry($path);
      
      echo json_encode(['success' => true, 'message' => 'Entry deleted successfully']);
      break;
      
    case 'getEntry':
      $path = $input['path'] ?? '';
      
      if( empty($path) )
        throw new Exception('Path is required');
        
      $entry = DataManager::loadEntry($path);
      echo json_encode(['success' => true, 'data' => $entry]);
      break;
      
    default:
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
}
catch( Exception $e ) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getNextFilesNr()
{
  $filesNrFile = 'user/Default/types/Apartment/files_nr.json';
  $dir = dirname($filesNrFile);
  
  if( ! is_dir($dir) )
    mkdir($dir, 0755, true);
    
  $currentNr = 1;
  if( file_exists($filesNrFile) )
  {
    $data = json_decode(file_get_contents($filesNrFile), true);
    $currentNr = ($data['last_nr'] ?? 0) + 1;
  }
  
  $data = ['last_nr' => $currentNr];
  file_put_contents($filesNrFile, json_encode($data));
  
  return str_pad($currentNr, 4, '0', STR_PAD_LEFT);
}
?>
