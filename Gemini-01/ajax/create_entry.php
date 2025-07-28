<?php

use App\System;
use Symfony\Component\Yaml\Yaml;

$system = new System();
header('Content-Type: application/json');

if (empty($_POST['type']) || empty($_POST['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input. Type and name are required.']);
    exit;
}

$typeId = $_POST['type'];
$name = $_POST['name'];
$description = $_POST['description'] ?? '';
$parentPath = $_POST['path'] ?? '';

$typeDef = $system->getType($typeId);
if ( ! $typeDef )
{
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid type specified.']);
  exit;
}

// --- Prepare Entry Data ---
$entryData = [
    'id' => $system->generateId($name),
    'type' => $typeId,
    'time' => date('Y-m-d H:i:s'),
    'name' => $name,
];

$yamlData = [];
if (!empty($typeDef['fields'])) {
    foreach ($typeDef['fields'] as $fieldDef) {
        $fieldId = $fieldDef['id'];
        if (isset($_POST[$fieldId])) {
            $yamlData[$fieldId] = $_POST[$fieldId];
        } elseif (isset($fieldDef['default'])) {
            $yamlData[$fieldId] = $fieldDef['default'];
        }
    }
}

// --- Handle Special Cases ---
if ($typeId === 'Apartment') {
    $filesNrPath = __DIR__ . '/../types/Apartment/files_nr.json';
    $filesNrData = json_decode(file_get_contents($filesNrPath), true);
    $newId = $filesNrData['last_id'] + 1;
    $filesNrData['last_id'] = $newId;
    file_put_contents($filesNrPath, json_encode($filesNrData, JSON_PRETTY_PRINT));
    $yamlData['files_nr'] = str_pad($newId, 4, '0', STR_PAD_LEFT);
}

$entryData['data'] = $yamlData;

// --- Create File/Folder ---
$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
$newPath = 'data' . ($parentPath ? '/' . $parentPath : '') . '/' . $safeName;

if (!mkdir($newPath, 0777, true)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create directory.']);
    exit;
}

// Handle Image Uploads for Apartments
if ($typeId === 'Apartment' && !empty($_FILES['images'])) {
    $imagesDir = $newPath . '/images';
    if (!is_dir($imagesDir) && !mkdir($imagesDir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create images directory.']);
        exit;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileType = mime_content_type($tmpName);
            if (in_array($fileType, $allowedTypes)) {
                $fileName = basename($_FILES['images']['name'][$key]);
                $destination = $imagesDir . '/' . $fileName;
                if (!move_uploaded_file($tmpName, $destination)) {
                    // You might want to log this error but not fail the whole request
                }
            }
        }
    }
}

$dataFileName = $system->getConfig()['dataFileName'] ?? '-this';
$mdFilePath = "{$newPath}/{$dataFileName}.md";

$frontMatter = Yaml::dump($entryData);
$fileContent = "---\n{$frontMatter}---\n{$description}";

if (file_put_contents($mdFilePath, $fileContent)) {
    echo json_encode(['success' => true, 'message' => 'Entry created successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write to file.']);
}
