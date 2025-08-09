<?php

$rel = isset($_POST['rel']) ? (string)$_POST['rel'] : '';
if( $rel === '' )
{
  echo json_encode([ 'ok' => false, 'error' => 'Missing rel' ]);
  exit;
}

if( ! isset($_FILES['file']) || ! is_array($_FILES['file']) )
{
  echo json_encode([ 'ok' => false, 'error' => 'Missing file' ]);
  exit;
}

$f = $_FILES['file'];
if( (int)($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK )
{
  echo json_encode([ 'ok' => false, 'error' => 'Upload error ' . (int)$f['error'] ]);
  exit;
}

$absDir = DataManager::absPath($rel);
if( ! is_dir($absDir) )
{
  echo json_encode([ 'ok' => false, 'error' => 'Target not found' ]);
  exit;
}

$name = (string)($f['name'] ?? 'image');
$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
$allowed = [ 'jpg','jpeg','png','webp','heic','heif' ];
if( ! in_array($ext, $allowed, true) )
{
  echo json_encode([ 'ok' => false, 'error' => 'Unsupported file type' ]);
  exit;
}

// Try to prefix with apartment files_nr from front matter
$filesNr = '';
$dataFile = Util::joinPath($absDir, Util::dataFileName() . '.md');
if( file_exists($dataFile) )
{
  [$fm, $body] = Util::readFrontMatter($dataFile);
  $filesNr = (string)($fm['files_nr'] ?? '');
}
$prefix = $filesNr !== '' ? $filesNr . '-' : '';
$base = $prefix . 'img-' . date('Ymd-His');
$targetName = Util::sanitizeName($base) . '.' . $ext;
$targetAbs = Util::joinPath($absDir, $targetName);

// Ensure unique
$i = 1;
while( file_exists($targetAbs) )
{
  $targetName = Util::sanitizeName($base . '-' . $i) . '.' . $ext;
  $targetAbs = Util::joinPath($absDir, $targetName);
  $i++;
}

if( ! move_uploaded_file($f['tmp_name'], $targetAbs) )
{
  echo json_encode([ 'ok' => false, 'error' => 'Failed to save file' ]);
  exit;
}

$relSaved = Util::relPathWithin(DataManager::dataRoot(), $targetAbs);

echo json_encode([ 'ok' => true, 'rel' => $relSaved, 'name' => $targetName ]);
