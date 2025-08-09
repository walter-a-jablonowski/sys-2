<?php

require_once 'vendor/autoload.php';
require_once 'lib/Util.php';
require_once 'lib/TypeManager.php';
require_once 'lib/DataManager.php';

use Symfony\Component\Yaml\Yaml;

header('Content-Type: application/json; charset=utf-8');

// Basic error handling to JSON
set_exception_handler(function($e)
{
  http_response_code(500);
  echo json_encode([ 'ok' => false, 'error' => $e->getMessage() ]);
});

set_error_handler(function($severity, $message, $file, $line)
{
  http_response_code(500);
  echo json_encode([ 'ok' => false, 'error' => "$message ($file:$line)" ]);
  return true;
});

$action = isset($_GET['a']) ? $_GET['a'] : '';

if( $action === '' )
{
  echo json_encode([ 'ok' => false, 'error' => 'Missing action' ]);
  exit;
}

// Allow only known actions
$allowed = [ 'list', 'loadEditForm', 'saveEntry', 'deleteEntry', 'getNextFileNumber', 'apartmentUpload' ];
if( ! in_array($action, $allowed ) )
{
  echo json_encode([ 'ok' => false, 'error' => 'Unknown action' ]);
  exit;
}

// Dispatch
switch( $action )
{
  case 'list':
    require 'ajax/list.php';
    break;
  case 'loadEditForm':
    require 'ajax/loadEditForm.php';
    break;
  case 'saveEntry':
    require 'ajax/saveEntry.php';
    break;
  case 'deleteEntry':
    require 'ajax/deleteEntry.php';
    break;
  case 'getNextFileNumber':
    require 'types/Apartment/ajax/getNextFileNumber.php';
    break;
  case 'apartmentUpload':
    require 'types/Apartment/ajax/uploadImage.php';
    break;
}
