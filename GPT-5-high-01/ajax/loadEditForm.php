<?php

// ajax.php already loaded autoload and libs

$raw = file_get_contents('php://input');
$payload = $raw ? json_decode($raw, true) : [];

$typeId = isset($payload['typeId']) ? (string)$payload['typeId'] : (isset($_GET['typeId']) ? (string)$_GET['typeId'] : '');
$data = (array)($payload['data'] ?? []);
$isNew = (bool)($payload['isNew'] ?? ($_GET['isNew'] ?? true));

if( $typeId === '' )
{
  echo json_encode([ 'ok' => false, 'error' => 'Missing typeId' ]);
  exit;
}

// Merge defaults
$merged = array_merge(TypeManager::defaultInstanceData($typeId), $data);
$html = DataManager::renderEditForm($typeId, $merged, $isNew);

echo json_encode([ 'ok' => true, 'html' => $html ]);
