<?php

$raw = file_get_contents('php://input');
$payload = $raw ? json_decode($raw, true) : [];

$rel = isset($payload['rel']) ? (string)$payload['rel'] : (isset($_GET['rel']) ? (string)$_GET['rel'] : '');
if( $rel === '' )
{
  echo json_encode([ 'ok' => false, 'error' => 'Missing rel' ]);
  exit;
}

$ok = DataManager::deletePath($rel);

echo json_encode([ 'ok' => (bool)$ok ]);
