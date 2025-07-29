<?php

require_once 'lib/DataManager.php';

$path = $input['path'] ?? 'data';
$entries = DataManager::getEntries($path);
echo json_encode(['success' => true, 'data' => $entries]);
?>
