<?php

$raw = file_get_contents('php://input');
$payload = $raw ? json_decode($raw, true) : [];

$parentRel = isset($payload['parentRel']) ? (string)$payload['parentRel'] : '';
$typeId = isset($payload['typeId']) ? (string)$payload['typeId'] : '';
$data = (array)($payload['data'] ?? []);
$existingRel = isset($payload['existingRel']) ? (string)$payload['existingRel'] : null;

if( $typeId === '' )
{
  echo json_encode([ 'ok' => false, 'error' => 'Missing typeId' ]);
  exit;
}

$res = DataManager::saveInstance($parentRel, $typeId, $data, $existingRel);

echo json_encode($res);
