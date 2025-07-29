<?php

require_once 'classes/TypeManager.php';

use Symfony\Component\Yaml\Yaml;

class InstanceManager 
{
  private $typeManager;
  private $config;

  public function __construct() 
  {
    $this->typeManager = new TypeManager();
    $this->config = Yaml::parseFile('config.yml');
  }

  public function loadInstance( $path ) 
  {
    $dataFile = $this->config['dataFileName'] . '.md';
    
    if( is_dir($path) ) {
      $instanceFile = "$path/$dataFile";
    } else {
      $instanceFile = $path;
    }

    if( ! file_exists($instanceFile) ) {
      return null;
    }

    $content = file_get_contents($instanceFile);
    
    // Parse front matter
    $frontMatter = [];
    $description = '';
    
    if( preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches) ) {
      $frontMatter = Yaml::parse($matches[1]);
      $description = trim($matches[2]);
    } else {
      $description = $content;
    }

    // Add description to front matter if it exists
    if( ! empty($description) ) {
      $frontMatter['description'] = $description;
    }

    return $frontMatter;
  }

  public function saveInstance( $path, $data ) 
  {
    $dataFile = $this->config['dataFileName'] . '.md';
    
    if( is_dir($path) || substr($path, -1) === '/' ) {
      $instanceFile = rtrim($path, '/') . "/$dataFile";
      $dir = rtrim($path, '/');
    } else {
      $instanceFile = $path;
      $dir = dirname($path);
    }

    // Create directory if it doesn't exist
    if( ! is_dir($dir) ) {
      mkdir($dir, 0755, true);
    }

    // Separate description from other fields
    $description = $data['description'] ?? '';
    unset($data['description']);

    // Create front matter
    $frontMatter = '';
    if( ! empty($data) ) {
      $frontMatter = "---\n" . Yaml::dump($data) . "---\n";
    }

    $content = $frontMatter . $description;
    
    return file_put_contents($instanceFile, $content) !== false;
  }

  public function createInstance( $parentPath, $name, $type, $data = [] ) 
  {
    // Generate ID
    $id = $this->typeManager->generateId($name);
    
    // Set default fields
    $instanceData = [
      'id' => $id,
      'type' => $type,
      'time' => date('Y-m-d H:i:s'),
      'name' => $name
    ];

    // Add special handling for Apartment type
    if( $type === 'Apartment' ) {
      $instanceData['files_nr'] = $this->typeManager->getNextFilesNr();
    }

    // Merge with provided data
    $instanceData = array_merge($instanceData, $data);

    // Determine path
    $instancePath = "$parentPath/$name";
    
    return $this->saveInstance($instancePath, $instanceData);
  }

  public function listInstances( $path ) 
  {
    if( ! is_dir($path) ) {
      return [];
    }

    $items = scandir($path);
    $instances = [];
    $resources = [];

    foreach( $items as $item ) {
      if( $item === '.' || $item === '..' ) {
        continue;
      }

      $itemPath = "$path/$item";
      $type = null;

      // Try to identify type
      if( is_dir($itemPath) ) {
        $dataFile = $itemPath . '/' . $this->config['dataFileName'] . '.md';
        if( file_exists($dataFile) ) {
          $instanceData = $this->loadInstance($itemPath);
          $type = $instanceData['type'] ?? $this->typeManager->identifyType($item);
        } else {
          // It's a group folder
          $resources[] = [
            'name' => $item,
            'path' => $itemPath,
            'type' => 'folder',
            'is_resource' => true
          ];
          continue;
        }
      } else {
        // Check if it's a single file instance
        if( preg_match('/\.md$/', $item) && $item !== $this->config['dataFileName'] . '.md' ) {
          $instanceData = $this->loadInstance($itemPath);
          $type = $instanceData['type'] ?? $this->typeManager->identifyType($item);
        } else {
          // It's a resource file
          $resources[] = [
            'name' => $item,
            'path' => $itemPath,
            'type' => 'file',
            'is_resource' => true,
            'size' => filesize($itemPath)
          ];
          continue;
        }
      }

      if( $type ) {
        $instanceData = $this->loadInstance($itemPath);
        $instances[] = [
          'name' => $item,
          'path' => $itemPath,
          'type' => $type,
          'data' => $instanceData,
          'is_resource' => false
        ];
      }
    }

    // Sort instances by time (newest first)
    usort($instances, function( $a, $b ) {
      $timeA = $a['data']['time'] ?? '0000-00-00 00:00:00';
      $timeB = $b['data']['time'] ?? '0000-00-00 00:00:00';
      return strcmp($timeB, $timeA);
    });

    return ['instances' => $instances, 'resources' => $resources];
  }

  public function deleteInstance( $path ) 
  {
    if( is_dir($path) ) {
      return $this->deleteDirectory($path);
    } else {
      return unlink($path);
    }
  }

  private function deleteDirectory( $dir ) 
  {
    if( ! is_dir($dir) ) {
      return false;
    }

    $items = scandir($dir);
    foreach( $items as $item ) {
      if( $item === '.' || $item === '..' ) {
        continue;
      }

      $itemPath = "$dir/$item";
      if( is_dir($itemPath) ) {
        $this->deleteDirectory($itemPath);
      } else {
        unlink($itemPath);
      }
    }

    return rmdir($dir);
  }
}
