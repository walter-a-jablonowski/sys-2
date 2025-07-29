<?php

require_once 'lib/DataManager.php';

$path = $input['path'] ?? '';
$renderer = $input['renderer'] ?? 'read_only';

if( empty($path) )
  throw new Exception('Path is required');
  
$entry = DataManager::loadEntry($path);
$type = $entry['type'] ?? null;

if( $type )
{
  $rendererFile = "../types/$type/$renderer.php";
  if( file_exists($rendererFile) )
  {
    ob_start();
    include $rendererFile;
    $html = ob_get_clean();
    echo json_encode(['success' => true, 'html' => $html]);
    return;
  }
}

// Fallback to basic rendering
$html = "<p>No specific renderer found for type: $type</p>";
echo json_encode(['success' => true, 'html' => $html]);
?>
