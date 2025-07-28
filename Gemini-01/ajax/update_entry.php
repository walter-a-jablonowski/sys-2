<?php

use App\System;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

$system = new System();

if (empty($_POST['path'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input. Path is required.']);
    exit;
}

$path = $_POST['path'];

// Security check
if (strpos($path, '..') !== false || !preg_match('/^data[\\\/]/', $path))
{
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid path.']);
    exit;
}

$dataFileName = $system->getConfig()['dataFileName'] ?? '-this';
$mdFilePath = "{$path}/{$dataFileName}.md";

if ( ! file_exists($mdFilePath) )
{
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Entry file not found.']);
  exit;
}

// Read existing data
$existingEntry = $system->parseEntry($path);
if ( ! $existingEntry )
{
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not parse existing entry.']);
    exit;
}

// Prepare new data
$description = $_POST['description'] ?? $existingEntry['content'] ?? '';
$frontMatter = $existingEntry['frontMatter'];

// Update name if provided
if (isset($_POST['name'])) {
    $frontMatter['name'] = $_POST['name'];
}

// Update type-specific fields
$typeDef = $system->getType($frontMatter['type']);
if (!empty($typeDef['fields'])) {
    foreach ($typeDef['fields'] as $fieldDef) {
        $fieldId = $fieldDef['id'];
        if (isset($_POST[$fieldId])) {
            $frontMatter['data'][$fieldId] = $_POST[$fieldId];
        }
    }
}

// Handle Image Uploads for Apartments
if ($frontMatter['type'] === 'Apartment' && !empty($_FILES['images'])) {
    $imagesDir = $path . '/images';
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
                    // Log error maybe?
                }
            }
        }
    }
}

$newFrontMatterYaml = Yaml::dump($frontMatter);
$newFileContent = "---\n{$newFrontMatterYaml}---\n{$description}";

if (file_put_contents($mdFilePath, $newFileContent) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to write updated entry.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Entry updated successfully.']);
