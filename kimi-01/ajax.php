<?php

// Include composer autoloader
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

// Error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

header('Content-Type: application/json');

try
{
  $action = $_GET['action'] ?? '';
  
  switch ($action)
  {
    case 'listEntries':
      echo json_encode(listEntries($_GET['path'] ?? ''));
      break;
      
    case 'listResources':
      echo json_encode(listResources($_GET['path'] ?? ''));
      break;
      
    case 'addEntry':
      $data = json_decode(file_get_contents('php://input'), true);
      echo json_encode(addEntry($data));
      break;
      
    case 'getEditForm':
      echo json_encode(getEditForm($_GET['id'], $_GET['path']));
      break;
      
    case 'saveEntry':
      $data = json_decode(file_get_contents('php://input'), true);
      echo json_encode(saveEntry($data));
      break;
      
    case 'deleteEntry':
      $data = json_decode(file_get_contents('php://input'), true);
      echo json_encode(deleteEntry($data['id'], $data['path']));
      break;
      
    case 'uploadImage':
      echo json_encode(uploadImage());
      break;
      
    default:
      throw new Exception('Invalid action');
  }
}
catch (Exception $e)
{
  echo json_encode(['error' => $e->getMessage()]);
}

// Helper functions
function listEntries($path)
{
  $fullPath = 'data/' . $path;
  $fullPath = rtrim($fullPath, '/');
  
  if (!is_dir($fullPath))
  {
    return ['entries' => []];
  }
  
  $entries = [];
  $items = scandir($fullPath);
  
  foreach ($items as $item)
  {
    if ($item === '.' || $item === '..') continue;
    
    $itemPath = $fullPath . '/' . $item;
    $relativePath = $path ? $path . '/' . $item : $item;
    
    // Skip resource files
    if (is_file($itemPath) && $item !== '-this.md' && !preg_match('/^\d{6}\s*-/', $item))
    {
      continue;
    }
    
    $entry = loadEntry($item, $relativePath, $itemPath);
    if ($entry)
    {
      $entries[] = $entry;
    }
  }
  
  // Sort by time descending
  usort($entries, function($a, $b) {
    return strcmp($b['time'], $a['time']);
  });
  
  return ['entries' => $entries];
}

function loadEntry($name, $relativePath, $fullPath)
{
  // Load configuration
  $config = Yaml::parseFile('config.yml');
  $dataFileName = $config['dataFileName'];
  
  $isDirectory = is_dir($fullPath);
  $entryPath = $isDirectory ? $fullPath . '/' . $dataFileName . '.md' : $fullPath;
  
  if (!file_exists($entryPath))
  {
    return null;
  }
  
  // Parse front matter
  $content = file_get_contents($entryPath);
  $parts = explode("---\n", $content, 3);
  
  if (count($parts) < 3)
  {
    $frontMatter = [];
    $description = $content;
  }
  else
  {
    $frontMatter = Yaml::parse($parts[1]) ?: [];
    $description = trim($parts[2]);
  }
  
  // Determine type
  $type = determineType($name, $frontMatter);
  if (!$type)
  {
    return null;
  }
  
  // Load type definition
  $typeDef = Yaml::parseFile("types/$type/def.yml");
  
  // Create entry data
  $entry = [
    'id' => $frontMatter['id'] ?? generateId($name),
    'name' => $frontMatter['name'] ?? pathinfo($name, PATHINFO_FILENAME),
    'description' => $description,
    'time' => $frontMatter['time'] ?? date('Y-m-d H:i:s'),
    'type' => $type,
    'path' => $relativePath,
    'isDirectory' => $isDirectory,
    'data' => $frontMatter
  ];
  
  // Generate list content
  $entry['listContent'] = renderListCell($type, $entry);
  
  return $entry;
}

function determineType($name, $frontMatter)
{
  // Check front matter first
  if (isset($frontMatter['type']))
  {
    return $frontMatter['type'];
  }
  
  // Check type identification patterns
  $types = ['Activity', 'Info', 'Apartment'];
  
  foreach ($types as $type)
  {
    $typeDef = Yaml::parseFile("types/$type/def.yml");
    if (isset($typeDef['typeIdentification']))
    {
      if (preg_match('/' . $typeDef['typeIdentification'] . '/', $name))
      {
        return $type;
      }
    }
  }
  
  return null;
}

function generateId($name)
{
  // Convert to words and capitalize first letters
  $words = preg_split('/[^a-zA-Z0-9]+/', $name);
  $id = '';
  foreach ($words as $word)
  {
    $id .= ucfirst(strtolower($word));
  }
  
  // Remove non-alphanumeric
  $id = preg_replace('/[^a-zA-Z0-9]/', '', $id);
  
  // Add user and date
  $id .= '-Default-' . date('ymdHis');
  
  return $id;
}

function renderListCell($type, $entry)
{
  $renderer = "types/$type/list.php";
  if (file_exists($renderer))
  {
    ob_start();
    include $renderer;
    return ob_get_clean();
  }
  
  // Default renderer
  $html = '<div>';
  $html .= '<h6 class="mb-1">' . htmlspecialchars($entry['name']) . '</h6>';
  $html .= '<small class="text-muted">' . htmlspecialchars($entry['type']) . '</small>';
  $html .= '<small class="text-muted d-block">' . htmlspecialchars($entry['time']) . '</small>';
  $html .= '</div>';
  
  return $html;
}

function listResources($path)
{
  $fullPath = 'data/' . $path;
  $fullPath = rtrim($fullPath, '/');
  
  if (!is_dir($fullPath))
  {
    return ['resources' => []];
  }
  
  $resources = [];
  $items = scandir($fullPath);
  
  foreach ($items as $item)
  {
    if ($item === '.' || $item === '..') continue;
    
    $itemPath = $fullPath . '/' . $item;
    $relativePath = $path ? $path . '/' . $item : $item;
    
    // Skip entry files
    if ($item === '-this.md') continue;
    if (preg_match('/^\d{6}\s*-/', $item) && is_file($itemPath)) continue;
    
    $resources[] = [
      'name' => $item,
      'url' => 'data/' . $relativePath,
      'isDirectory' => is_dir($itemPath)
    ];
  }
  
  return ['resources' => $resources];
}

function addEntry($data)
{
  $type = $data['type'];
  $name = $data['name'];
  $description = $data['description'];
  $path = $data['path'];
  
  // Validate
  if (!$type || !$name)
  {
    throw new Exception('Type and name are required');
  }
  
  // Generate filename
  $filename = '';
  if ($type === 'Info')
  {
    $filename = date('ymd') . ' - ' . $name . '.md';
  }
  else
  {
    $filename = $name;
  }
  
  $fullPath = 'data/' . $path;
  $fullPath = rtrim($fullPath, '/');
  
  if ($type === 'Info')
  {
    // Create file
    $filePath = $fullPath . '/' . $filename;
    if (file_exists($filePath))
    {
      throw new Exception('File already exists');
    }
    
    $content = createEntryContent($type, $name, $description);
    file_put_contents($filePath, $content);
  }
  else
  {
    // Create directory
    $dirPath = $fullPath . '/' . $filename;
    if (is_dir($dirPath))
    {
      throw new Exception('Directory already exists');
    }
    
    mkdir($dirPath, 0777, true);
    
    $content = createEntryContent($type, $name, $description);
    file_put_contents($dirPath . '/-this.md', $content);
  }
  
  return ['success' => true];
}

function createEntryContent($type, $name, $description)
{
  $id = generateId($name);
  $time = date('Y-m-d H:i:s');
  
  $content = "---\n";
  $content .= "id: $id\n";
  $content .= "type: $type\n";
  $content .= "name: $name\n";
  $content .= "time: $time\n";
  
  // Add type-specific fields
  $typeDef = Yaml::parseFile("types/$type/def.yml");
  foreach ($typeDef as $key => $field)
  {
    if (is_array($field) && isset($field['type']))
    {
      $content .= "$key: \n";
    }
  }
  
  $content .= "---\n\n";
  $content .= $description;
  
  return $content;
}

function getEditForm($id, $path)
{
  $entry = findEntry($id, $path);
  if (!$entry)
  {
    throw new Exception('Entry not found');
  }
  
  $renderer = "types/{$entry['type']}/edit.php";
  if (file_exists($renderer))
  {
    ob_start();
    include $renderer;
    $form = ob_get_clean();
  }
  else
  {
    $form = createDefaultEditForm($entry);
  }
  
  return ['form' => $form];
}

function createDefaultEditForm($entry)
{
  $html = '<form id="editForm">';
  $html .= '<input type="hidden" name="id" value="' . htmlspecialchars($entry['id']) . '">';
  $html .= '<input type="hidden" name="path" value="' . htmlspecialchars($entry['path']) . '">';
  $html .= '<input type="hidden" name="type" value="' . htmlspecialchars($entry['type']) . '">';
  
  $html .= '<div class="mb-3">';
  $html .= '<label class="form-label">Name</label>';
  $html .= '<input type="text" class="form-control" name="name" value="' . htmlspecialchars($entry['name']) . '">';
  $html .= '</div>';
  
  $html .= '<div class="mb-3">';
  $html .= '<label class="form-label">Description</label>';
  $html .= '<textarea class="form-control" name="description" rows="5">' . htmlspecialchars($entry['description']) . '</textarea>';
  $html .= '</div>';
  
  // Add type-specific fields
  $typeDef = Yaml::parseFile("types/{$entry['type']}/def.yml");
  foreach ($typeDef as $key => $field)
  {
    if (is_array($field) && isset($field['type']) && !in_array($key, ['id', 'time', 'name', 'description']))
    {
      $value = $entry['data'][$key] ?? '';
      $html .= '<div class="mb-3">';
      $html .= '<label class="form-label">' . ucfirst($key) . '</label>';
      
      if (isset($field['enum']))
      {
        $html .= '<select class="form-select" name="' . $key . '">';
        foreach ($field['enum'] as $option)
        {
          $selected = $value === $option ? ' selected' : '';
          $html .= '<option value="' . htmlspecialchars($option) . '"' . $selected . '>' . htmlspecialchars($option) . '</option>';
        }
        $html .= '</select>';
      }
      else
      {
        $html .= '<input type="text" class="form-control" name="' . $key . '" value="' . htmlspecialchars($value) . '">';
      }
      
      $html .= '</div>';
    }
  }
  
  // Camera upload for Apartment
  if ($entry['type'] === 'Apartment')
  {
    $html .= '<div class="mb-3">';
    $html .= '<button type="button" class="btn btn-primary" onclick="uploadCameraImage(\'' . $entry['id'] . '\', \'' . $entry['path'] . '\')">';
    $html .= '<i class="bi bi-camera"></i> Upload Camera Image';
    $html .= '</button>';
    $html .= '</div>';
    
    // Show existing images
    if (!empty($entry['data']['files_nr']))
    {
      $files = json_decode($entry['data']['files_nr'], true) ?: [];
      if (!empty($files))
      {
        $html .= '<div class="mb-3">';
        $html .= '<label class="form-label">Images</label>';
        $html .= '<div class="row">';
        foreach ($files as $file)
        {
          $html .= '<div class="col-6 col-md-4 mb-2">';
          $html .= '<img src="data/' . htmlspecialchars($entry['path']) . '/' . htmlspecialchars($file) . '" class="img-fluid rounded" alt="Image">';
          $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
      }
    }
  }
  
  $html .= '<div class="modal-footer">';
  $html .= '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>';
  $html .= '<button type="button" class="btn btn-primary" onclick="saveEntry()">Save</button>';
  $html .= '</div>';
  
  $html .= '</form>';
  
  return $html;
}

function saveEntry($data)
{
  $entry = findEntry($data['id'], $data['path']);
  if (!$entry)
  {
    throw new Exception('Entry not found');
  }
  
  $fullPath = 'data/' . $data['path'];
  
  if (is_dir($fullPath))
  {
    $filePath = $fullPath . '/-this.md';
  }
  else
  {
    $filePath = $fullPath;
  }
  
  if (!file_exists($filePath))
  {
    throw new Exception('Entry file not found');
  }
  
  // Parse existing content
  $content = file_get_contents($filePath);
  $parts = explode("---\n", $content, 3);
  
  $frontMatter = [];
  if (count($parts) >= 3)
  {
    $frontMatter = Yaml::parse($parts[1]) ?: [];
    $description = trim($parts[2]);
  }
  else
  {
    $description = $content;
  }
  
  // Update fields
  foreach ($data as $key => $value)
  {
    if (!in_array($key, ['id', 'path']))
    {
      $frontMatter[$key] = $value;
    }
  }
  
  // Rebuild content
  $newContent = "---\n" . Yaml::dump($frontMatter, 2, 2) . "---\n\n" . $data['description'];
  
  file_put_contents($filePath, $newContent);
  
  return ['success' => true];
}

function deleteEntry($id, $path)
{
  $entry = findEntry($id, $path);
  if (!$entry)
  {
    throw new Exception('Entry not found');
  }
  
  $fullPath = 'data/' . $path;
  
  if (is_dir($fullPath))
  {
    // Remove directory and contents
    removeDirectory($fullPath);
  }
  else
  {
    unlink($fullPath);
  }
  
  return ['success' => true];
}

function uploadImage()
{
  $entryId = $_POST['entryId'];
  $entryPath = $_POST['entryPath'];
  
  if (!isset($_FILES['image']))
  {
    throw new Exception('No image uploaded');
  }
  
  $file = $_FILES['image'];
  if ($file['error'] !== UPLOAD_ERR_OK)
  {
    throw new Exception('Upload failed');
  }
  
  // Validate image
  $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  if (!in_array($file['type'], $allowedTypes))
  {
    throw new Exception('Invalid image type');
  }
  
  // Find entry
  $entry = findEntry($entryId, $entryPath);
  if (!$entry || $entry['type'] !== 'Apartment')
  {
    throw new Exception('Invalid entry');
  }
  
  // Generate filename
  $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
  $filename = 'img_' . date('YmdHis') . '.' . $extension;
  
  $fullPath = 'data/' . $entryPath;
  $targetPath = $fullPath . '/' . $filename;
  
  if (!move_uploaded_file($file['tmp_name'], $targetPath))
  {
    throw new Exception('Failed to save image');
  }
  
  // Update files_nr field
  $entryFile = is_dir($fullPath) ? $fullPath . '/-this.md' : $fullPath;
  $content = file_get_contents($entryFile);
  $parts = explode("---\n", $content, 3);
  
  $frontMatter = [];
  if (count($parts) >= 3)
  {
    $frontMatter = Yaml::parse($parts[1]) ?: [];
  }
  
  $files = json_decode($frontMatter['files_nr'] ?? '[]', true) ?: [];
  $files[] = $filename;
  $frontMatter['files_nr'] = json_encode($files);
  
  $newContent = "---\n" . Yaml::dump($frontMatter, 2, 2) . "---\n" . (count($parts) >= 3 ? $parts[2] : '');
  file_put_contents($entryFile, $newContent);
  
  return ['success' => true, 'filename' => $filename];
}

function findEntry($id, $path)
{
  $entries = listEntries($path);
  foreach ($entries['entries'] as $entry)
  {
    if ($entry['id'] === $id)
    {
      return $entry;
    }
  }
  return null;
}

function removeDirectory($dir)
{
  if (!is_dir($dir)) return;
  
  $items = scandir($dir);
  foreach ($items as $item)
  {
    if ($item === '.' || $item === '..') continue;
    
    $path = $dir . '/' . $item;
    if (is_dir($path))
    {
      removeDirectory($path);
    }
    else
    {
      unlink($path);
    }
  }
  
  rmdir($dir);
}
?>
