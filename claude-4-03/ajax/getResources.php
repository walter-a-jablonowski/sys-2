<?php

require_once '../lib/DataManager.php';

$path = $input['path'] ?? 'data';
$resources = DataManager::getResources($path);
echo json_encode(['success' => true, 'data' => $resources]);
?>
