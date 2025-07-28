<?php

use App\System;

require_once __DIR__ . '/../vendor/autoload.php';

$system = new System();

$path = $_GET['path'] ?? null;

if ( ! $path )
{
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Path is required.']);
  exit;
}

// Basic security check
if (strpos($path, '..') !== false || !preg_match('/^data[\\\/]/', $path))
{
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid path.']);
    exit;
}

$entry = $system->parseEntry($path);

if ( $entry )
{
  echo json_encode(['success' => true, 'data' => $entry]);
}
else
{
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Entry not found.']);
}
