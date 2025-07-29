<?php

require_once 'lib/Config.php';
require_once 'lib/TypeManager.php';
require_once 'lib/DataManager.php';

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
  $data['files_nr'] = getNextFilesNr();
  $data['state'] = 'new';
  $data['priority'] = 3;
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
