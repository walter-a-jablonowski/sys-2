<?php

/**
 * Main entry point for the application
 */

// Include Composer autoloader
require_once 'vendor/autoload.php';

// Load configuration
$config = [];
if( file_exists('config.yml') ) {
  $yaml = file_get_contents('config.yml');
  // We'll implement YAML parsing later
}

// Set default config values
$config['dataFileName'] = $config['dataFileName'] ?? '-this';

// Route based on URL parameters
$action = $_GET['action'] ?? 'list';
$path = $_GET['path'] ?? '';

// Include necessary files
require_once 'lib/utils.php';

// Handle routing
switch( $action ) {
  case 'view':
    // View a specific item
    include 'lib/view.php';
    break;
  
  case 'list':
  default:
    // List items at a path
    include 'lib/list.php';
    break;
}
