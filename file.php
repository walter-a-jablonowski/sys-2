<?php

/**
 * Serves a resource file out of the database.
 *
 * Everything goes through Db::clean(), so no request can leave the database
 * or reach the hidden .sys folder.
 */

require __DIR__ . '/vendor/autoload.php';

use Sys\App;
use Sys\Failure;

$app = App::boot(__DIR__);

try {

  $rel = $app->db->clean($_GET['path'] ?? '');
  $abs = $app->db->abs($rel);

  if( $rel === '' || ! is_file($abs))
    throw Failure::notFound($rel);
}
catch( Failure $e ) {
  http_response_code($e->status);
  header('Content-Type: text/plain; charset=utf-8');
  exit($e->getMessage());
}

$types = [
  'png'  => 'image/png',   'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
  'gif'  => 'image/gif',   'webp' => 'image/webp', 'svg' => 'image/svg+xml',
  'pdf'  => 'application/pdf',
  'txt'  => 'text/plain',  'md'  => 'text/plain', 'csv' => 'text/plain',
  'json' => 'application/json', 'yml' => 'text/plain', 'yaml' => 'text/plain'
];

$ext  = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
$mime = $types[$ext] ?? 'application/octet-stream';
$name = basename($abs);

header("Content-Type: {$mime}" . (strpos($mime, 'text/') === 0 ? '; charset=utf-8' : ''));
header('Content-Length: ' . filesize($abs));
header('X-Content-Type-Options: nosniff');

if( isset($_GET['download']) || $mime === 'application/octet-stream' )
  header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');

readfile($abs);
