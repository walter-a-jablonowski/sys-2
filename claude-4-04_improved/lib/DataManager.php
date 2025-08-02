<?php

use Symfony\Component\Yaml\Yaml;

class DataManager
{
  public $config;
  private $dataPath = 'data';
  private $typesPath = 'types';
  
  public function __construct()
  {
    $this->config = Yaml::parseFile('config.yml');
  }
  
  // Get current level data from path
  public function getCurrentLevel( $path )
  {
    if( empty($path) )
      return null;
    
    $fullPath = $this->dataPath . '/' . $path;
    $dataFile = $fullPath . '/' . $this->config['dataFileName'] . '.md';
    
    if( file_exists($dataFile) )
    {
      return $this->parseDataFile($dataFile);
    }
    
    return null;
  }
  
  // Get all entries at current path
  public function getEntries( $path )
  {
    $fullPath = empty($path) ? $this->dataPath : $this->dataPath . '/' . $path;
    
    if( ! is_dir($fullPath) )
      return [];
    
    $entries = [];
    $items = scandir($fullPath);
    
    foreach( $items as $item )
    {
      if( $item === '.' || $item === '..' )
        continue;
      
      $itemPath = $fullPath . '/' . $item;
      $relativePath = empty($path) ? $item : $path . '/' . $item;
      
      // Check if it's a typed entry
      if( is_dir($itemPath) )
      {
        $dataFile = $itemPath . '/' . $this->config['dataFileName'] . '.md';
        if( file_exists($dataFile) )
        {
          $entry = $this->parseDataFile($dataFile);
          $entry['path'] = $relativePath;
          $entries[] = $entry;
        }
      }
      elseif( is_file($itemPath) && pathinfo($item, PATHINFO_EXTENSION) === 'md' )
      {
        $entry = $this->parseDataFile($itemPath);
        $entry['path'] = $relativePath;
        $entries[] = $entry;
      }
    }
    
    // Sort by time (newest first)
    usort($entries, function($a, $b) {
      return strcmp($b['time'], $a['time']);
    });
    
    return $entries;
  }
  
  // Get resources at path
  public function getResourcesAtPath( $path )
  {
    $fullPath = empty($path) ? $this->dataPath : $this->dataPath . '/' . $path;
    
    if( ! is_dir($fullPath) )
      return [];
    
    $resources = [];
    $items = scandir($fullPath);
    
    foreach( $items as $item )
    {
      if( $item === '.' || $item === '..' || $item === $this->config['dataFileName'] . '.md' )
        continue;
      
      $itemPath = $fullPath . '/' . $item;
      
      // Skip typed entries
      if( is_dir($itemPath) && file_exists($itemPath . '/' . $this->config['dataFileName'] . '.md') )
        continue;
      
      if( is_file($itemPath) && $this->identifyType($item) )
        continue;
      
      $resource = [
        'name' => $item,
        'path' => $itemPath,
        'type' => is_dir($itemPath) ? 'folder' : 'file',
        'icon' => $this->getResourceIcon($item, is_dir($itemPath)),
        'modified' => date('Y-m-d H:i', filemtime($itemPath))
      ];
      
      if( is_file($itemPath) )
      {
        $size = filesize($itemPath);
        $resource['size'] = $this->formatFileSize($size);
      }
      
      $resources[] = $resource;
    }
    
    return $resources;
  }
  
  // Parse data file with YAML front matter
  public function parseDataFile( $filePath )
  {
    $content = file_get_contents($filePath);
    
    if( preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches) )
    {
      $frontMatter = Yaml::parse($matches[1]);
      $description = trim($matches[2]);
      
      if( $description )
        $frontMatter['description'] = $description;
      
      return $frontMatter;
    }
    
    return [];
  }
  
  // Save entry data
  public function saveEntry( $data, $path = '' )
  {
    // Generate ID if not provided
    if( ! isset($data['id']) )
    {
      $data['id'] = $this->generateId($data['name']);
    }
    
    // Set time if not provided
    if( ! isset($data['time']) )
    {
      $data['time'] = date('Y-m-d H:i:s');
    }
    
    // Determine if this is a folder or file entry
    $type = $this->getTypeDefinition($data['type']);
    $isFolder = $this->shouldCreateFolder($data, $type);
    
    $basePath = empty($path) ? $this->dataPath : $this->dataPath . '/' . $path;
    
    if( $isFolder )
    {
      $entryPath = $basePath . '/' . $this->generateFileName($data);
      $dataFile = $entryPath . '/' . $this->config['dataFileName'] . '.md';
      
      if( ! is_dir($entryPath) )
        mkdir($entryPath, 0755, true);
    }
    else
    {
      $fileName = $this->generateFileName($data) . '.md';
      $dataFile = $basePath . '/' . $fileName;
      
      if( ! is_dir($basePath) )
        mkdir($basePath, 0755, true);
    }
    
    // Prepare YAML front matter
    $frontMatter = $data;
    $description = $frontMatter['description'] ?? '';
    unset($frontMatter['description']);
    
    // Create file content
    $content = "---\n" . Yaml::dump($frontMatter) . "---\n";
    if( $description )
      $content .= "\n" . $description;
    
    file_put_contents($dataFile, $content);
    
    return $dataFile;
  }
  
  // Delete entry
  public function deleteEntry( $path )
  {
    $fullPath = $this->dataPath . '/' . $path;
    
    if( is_dir($fullPath) )
    {
      $this->deleteDirectory($fullPath);
    }
    elseif( is_file($fullPath) )
    {
      unlink($fullPath);
    }
    else
    {
      throw new Exception("Entry not found: $path");
    }
  }
  
  // Get available types for current context
  public function getAvailableTypes( $path = '' )
  {
    $types = [];
    
    // Get all type definitions
    $typeItems = scandir($this->typesPath);
    foreach( $typeItems as $item )
    {
      if( $item === '.' || $item === '..' || $item === 'def.yml' )
        continue;
      
      $typePath = $this->typesPath . '/' . $item;
      if( is_dir($typePath) && file_exists($typePath . '/def.yml') )
      {
        $typeDef = Yaml::parseFile($typePath . '/def.yml');
        $types[] = [
          'id' => $typeDef['id'],
          'name' => $typeDef['name']
        ];
      }
    }
    
    // TODO: Filter based on parent type's allowedSubTypes
    
    return $types;
  }
  
  // Get type renderer path
  public function getTypeRenderer( $typeId, $renderer )
  {
    $rendererPath = $this->typesPath . '/' . $typeId . '/' . $renderer . '.php';
    return file_exists($rendererPath) ? $rendererPath : null;
  }
  
  // Get type definition
  public function getTypeDefinition( $typeId )
  {
    $defPath = $this->typesPath . '/' . $typeId . '/def.yml';
    return file_exists($defPath) ? Yaml::parseFile($defPath) : null;
  }
  
  // Identify type from filename
  public function identifyType( $fileName )
  {
    $typeItems = scandir($this->typesPath);
    
    foreach( $typeItems as $item )
    {
      if( $item === '.' || $item === '..' || $item === 'def.yml' )
        continue;
      
      $typePath = $this->typesPath . '/' . $item;
      if( is_dir($typePath) && file_exists($typePath . '/def.yml') )
      {
        $typeDef = Yaml::parseFile($typePath . '/def.yml');
        
        if( isset($typeDef['typeIdentification']) )
        {
          $patterns = is_array($typeDef['typeIdentification']) 
            ? $typeDef['typeIdentification'] 
            : [$typeDef['typeIdentification']];
          
          foreach( $patterns as $pattern )
          {
            if( preg_match('/' . $pattern . '/', $fileName) )
            {
              return $typeDef['id'];
            }
          }
        }
      }
    }
    
    return null;
  }
  
  // Generate unique ID
  private function generateId( $name )
  {
    // Convert to title case and remove non-alphanumeric
    $words = explode(' ', $name);
    $titleCase = '';
    foreach( $words as $word )
    {
      $titleCase .= ucfirst(strtolower($word));
    }
    $clean = preg_replace('/[^a-zA-Z0-9]/', '', $titleCase);
    
    // Add user and timestamp
    $user = 'Default'; // TODO: Get from session/config
    $timestamp = date('ymdHis');
    
    return $clean . '-' . $user . '-' . $timestamp;
  }
  
  // Generate filename from data
  private function generateFileName( $data )
  {
    $type = $this->getTypeDefinition($data['type']);
    
    // Handle special filename patterns for Activity type
    if( $data['type'] === 'Activity' || ($type && isset($type['derivedFrom']) && $type['derivedFrom'] === 'Activity') )
    {
      if( isset($data['state']) && $data['state'] === 'done' )
      {
        $prefix = date('ymd'); // YYMMDD format
      }
      else
      {
        $prefix = $data['priority'] ?? '1';
      }
      
      return $prefix . ' - ' . $data['name'];
    }
    
    // Handle Info type pattern
    if( $data['type'] === 'Info' )
    {
      return '(i) ' . $data['name'];
    }
    
    // Default pattern
    return $data['name'];
  }
  
  // Determine if entry should be folder or file
  private function shouldCreateFolder( $data, $type )
  {
    // Info type is always a file
    if( $data['type'] === 'Info' )
      return false;
    
    // Activity and derived types are folders
    if( $data['type'] === 'Activity' || ($type && isset($type['derivedFrom']) && $type['derivedFrom'] === 'Activity') )
      return true;
    
    // Default to folder for other types
    return true;
  }
  
  // Get resource icon
  private function getResourceIcon( $name, $isDir )
  {
    if( $isDir )
      return '📁';
    
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    
    switch( $ext )
    {
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'gif':
      case 'webp':
        return '🖼️';
      case 'pdf':
        return '📄';
      case 'doc':
      case 'docx':
        return '📝';
      case 'xls':
      case 'xlsx':
        return '📊';
      default:
        return '📄';
    }
  }
  
  // Format file size
  private function formatFileSize( $bytes )
  {
    if( $bytes >= 1048576 )
      return round($bytes / 1048576, 1) . ' MB';
    elseif( $bytes >= 1024 )
      return round($bytes / 1024, 1) . ' KB';
    else
      return $bytes . ' B';
  }
  
  // Delete directory recursively
  private function deleteDirectory( $dir )
  {
    $files = scandir($dir);
    foreach( $files as $file )
    {
      if( $file === '.' || $file === '..' )
        continue;
      
      $filePath = $dir . '/' . $file;
      if( is_dir($filePath) )
        $this->deleteDirectory($filePath);
      else
        unlink($filePath);
    }
    rmdir($dir);
  }
}
