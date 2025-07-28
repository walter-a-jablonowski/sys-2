<?php

require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');

// Basic router
$action = $_GET['action'] ?? null;

if ( ! $action || ! preg_match('/^[a-z_]+$/', $action) )
{
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid action.']);
  exit;
}

$handlerFile = __DIR__ . "/ajax/{$action}.php";

if ( file_exists($handlerFile) )
{
  try
  {
    // The handler file is expected to handle the request and echo a json response
    require $handlerFile;
  }
  catch ( Exception $e )
  {
    http_response_code(500);
    // In a real app, log the error instead of exposing it
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
  }
}
else
{
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Action handler not found.']);
}
