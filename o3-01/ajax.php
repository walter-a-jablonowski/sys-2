<?php

require 'core/bootstrap.php';
require 'core/helpers.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

switch( $action ) {
  case 'apt_upload_img':
    apt_upload_img();
    break;
    case 'add':
    handle_add();
    break;
  case 'delete':
    handle_delete();
    break;
  default:
    echo json_encode(['error'=>'Unknown action']);
}

// -------------------------------------------------
function handle_add() : void
{
  $input = json_decode(file_get_contents('php://input'), true);
  if( ! $input || empty($input['name']) || empty($input['type']) ) {
    echo json_encode(['error'=>'Invalid data']);
    return;
  }
  [$globalDef, $types] = list_types();
  $type = $input['type'];
  if( ! isset($types[$type]) ) {
    echo json_encode(['error'=>'Unknown type']);
    return;
  }
  $relPath = trim($input['path'] ?? '', '/');
  $baseDir = DATA_DIR . ($relPath ? '/' . $relPath : '');
  if( ! is_dir($baseDir) ) {
    echo json_encode(['error'=>'Invalid path']);
    return;
  }
  $id = generate_id($input['name']);
  $instDir = "$baseDir/$id";
  if( ! mkdir($instDir, 0777, true) ) {
    echo json_encode(['error'=>'Could not create folder']);
    return;
  }
  // build front matter
  $yaml = [
    'id'   => $id,
    'type' => $type,
    'time' => date('Y-m-d H:i:s'),
    'name' => $input['name'],
    'description' => $input['description'] ?? ''
  ];
  // special files_nr for Apartment
  if( $type === 'Apartment' ) {
    $ctrFile = TYPES_DIR . '/Apartment/files_nr.json';
    $ctr = 0;
    if( file_exists($ctrFile) ) $ctr = (int)file_get_contents($ctrFile);
    $ctr++;
    file_put_contents($ctrFile, $ctr);
    $yaml['files_nr'] = str_pad($ctr, 4, '0', STR_PAD_LEFT);
  }
  $dataFile = $instDir . '/' . app_config()['dataFileName'] . '.md';
  file_put_contents($dataFile, build_front_matter($yaml, ''));
  echo json_encode(['success'=>true]);
}

function handle_delete() : void
{
  $input = json_decode(file_get_contents('php://input'), true);
  if( empty($input['path']) ) {
    echo json_encode(['error'=>'No path']);
    return;
  }
  $rel = trim($input['path'], '/');
  $target = DATA_DIR . '/' . $rel;
  if( ! file_exists($target) ) {
    echo json_encode(['error'=>'Not found']);
    return;
  }
  // Simple recursive delete
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
  foreach($it as $f) {
    $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
  }
  rmdir($target);
  echo json_encode(['success'=>true]);
}

function apt_upload_img() : void
{
  if( empty($_POST['id']) || empty($_FILES['file']) ) {
    echo json_encode(['error'=>'Missing parameters']);
    return;
  }
  $id = basename($_POST['id']);
  $cfg = app_config();
  $targetDir = DATA_DIR . '/' . $id;
  if( ! is_dir($targetDir) ) {
    echo json_encode(['error'=>'Invalid id']);
    return;
  }
  $file = $_FILES['file'];
  if( $file['error'] !== UPLOAD_ERR_OK ) {
    echo json_encode(['error'=>'Upload error']);
    return;
  }
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if( ! in_array($ext, ['jpg','jpeg','png']) ) {
    echo json_encode(['error'=>'Unsupported format']);
    return;
  }
  $name = 'img_' . date('YmdHis') . '.' . $ext;
  if( move_uploaded_file($file['tmp_name'], "$targetDir/$name") ) {
    echo json_encode(['success'=>true]);
  }
  else {
    echo json_encode(['error'=>'Save failed']);
  }
}
