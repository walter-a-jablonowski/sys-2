<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

// -------------------------------------------------
// Constants
// -------------------------------------------------

define('DATA_DIR', 'data');
define('TYPES_DIR', 'types');

define('CONFIG_FILE', 'config.yml');

// -------------------------------------------------
// Error handling
// -------------------------------------------------

function app_error_handler( $level, $message, $file, $line )
{
  $msg = "PHP Error: $message in $file:$line";
  echo "<div class='alert alert-danger m-2 small'>$msg</div>";
}

set_error_handler('app_error_handler');
