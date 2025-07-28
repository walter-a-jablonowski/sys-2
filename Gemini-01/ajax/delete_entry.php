<?php

use App\System;

require_once __DIR__ . '/../vendor/autoload.php';

$system = new System();
$input = json_decode(file_get_contents('php://input'), true);

if ( ! $input || empty($input['path']) )
{
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid input.']);
  exit;
}

$path = $input['path'];

// Basic security check to prevent directory traversal
if (strpos($path, '..') !== false || !preg_match('/^data[\\\/]/', $path))
{
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid path.']);
    exit;
}

if ( ! file_exists($path) )
{
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Item not found.']);
  exit;
}

if ( $system->deleteRecursively($path) )
{
  echo json_encode(['success' => true, 'message' => 'Entry deleted successfully.']);
}
else
{
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to delete entry.']);
}
