<?php

/**
 * View a specific item
 */

// Include Composer autoloader
require_once 'vendor/autoload.php';

// Load configuration
$config = [];
if( file_exists('config.yml') ) {
  $yaml = file_get_contents('config.yml');
  // We'll implement YAML parsing later
}

// Set default config values
$config['dataFileName'] = $config['dataFileName'] ?? '-this';

// Get path from URL parameter
$path = $_GET['path'] ?? '';

// Load type definitions
$types = loadTypeDefinitions();

// Get item data
$item = getItemAtPath($path, $config['dataFileName'], $types);

// Function to get item at a path
function getItemAtPath($path, $dataFileName, $types) {
  $fullPath = 'data/' . $path;
  
  if( !file_exists($fullPath) ) {
    return null;
  }
  
  // Check if it's a file or directory
  if( is_file($fullPath) ) {
    // Check if it's a data file
    if( pathinfo($fullPath, PATHINFO_FILENAME) === $dataFileName && pathinfo($fullPath, PATHINFO_EXTENSION) === 'md' ) {
      // Parse the data file
      $content = file_get_contents($fullPath);
      $data = parseFrontMatter($content);
      
      // If no type in front matter, try to identify from name
      if( !isset($data['type']) ) {
        $data['type'] = identifyType(basename($path), $types);
      }
      
      // Extract description from content after front matter
      if( !isset($data['description']) ) {
        $parts = explode("---", $content, 3);
        if( count($parts) >= 3 ) {
          $data['description'] = trim($parts[2]);
        }
      }
      
      return [
        'name' => basename($path),
        'path' => $path,
        'type' => $data['type'] ?? 'unknown',
        'data' => $data
      ];
    }
  } elseif( is_dir($fullPath) ) {
    // Check if directory contains a data file
    $dataFile = $fullPath . '/' . $dataFileName . '.md';
    
    if( file_exists($dataFile) ) {
      // Parse the data file
      $content = file_get_contents($dataFile);
      $data = parseFrontMatter($content);
      
      // If no type in front matter, try to identify from name
      if( !isset($data['type']) ) {
        $data['type'] = identifyType(basename($path), $types);
      }
      
      // Extract description from content after front matter
      if( !isset($data['description']) ) {
        $parts = explode("---", $content, 3);
        if( count($parts) >= 3 ) {
          $data['description'] = trim($parts[2]);
        }
      }
      
      return [
        'name' => basename($path),
        'path' => $path,
        'type' => $data['type'] ?? 'unknown',
        'data' => $data
      ];
    }
  }
  
  return null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($item['data']['name'] ?? 'Item') ?></title>
  <link rel="stylesheet" href="../styles.css">
  <script src="../controller.js"></script>
</head>
<body>
  <!-- Header bar -->
  <div class="header-bar d-flex justify-content-between align-items-center">
    <div>
      <?= htmlspecialchars($item['data']['name'] ?? 'Item') ?>
    </div>
    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Actions
      </button>
      <ul class="dropdown-menu" aria-labelledby="actionsDropdown">
        <li><a class="dropdown-item" href="#" onclick="editCurrentEntry()">Edit</a></li>
        <li><a class="dropdown-item" href="#" onclick="deleteCurrentEntry()">Delete</a></li>
        <li><a class="dropdown-item" href="#">Settings</a></li>
      </ul>
    </div>
  </div>
  
  <!-- Content area -->
  <div class="content-area">
    <?php if( $item && isset($types[$item['type']]) ): ?>
      <?php 
        $type = $item['type'];
        $data = $item['data'];
        include "../types/{$type}/ready_only.php";
      ?>
    <?php else: ?>
      <div class="alert alert-warning">Item not found or invalid type.</div>
    <?php endif; ?>
  </div>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Edit current entry
    function editCurrentEntry() {
      // Implementation would depend on current context
      alert('Edit functionality would be implemented here');
    }
    
    // Delete current entry
    function deleteCurrentEntry() {
      if( confirm('Are you sure you want to delete this entry?') ) {
        // Implementation would depend on current context
        alert('Delete functionality would be implemented here');
      }
    }
  </script>
</body>
</html>
