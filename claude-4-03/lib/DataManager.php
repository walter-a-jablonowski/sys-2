<?php

require_once 'Config.php';
require_once 'TypeManager.php';

use Symfony\Component\Yaml\Yaml;

class DataManager
{
  public static function getEntries( $path = 'data' )
  {
    $entries = [];
    
    if( ! is_dir($path) )
      return $entries;

    $items = scandir($path);
    foreach( $items as $item )
    {
      if( $item === '.' || $item === '..' )
        continue;
        
      $fullPath = "$path/$item";
      $entry = self::loadEntry($fullPath);
      
      if( $entry )
        $entries[] = $entry;
    }
    
    // Sort by time (newest first)
    usort($entries, function( $a, $b ) {
      return strcmp($b['time'], $a['time']);
    });
    
    return $entries;
  }

  public static function loadEntry( $path )
  {
    $name = basename($path);
    $isDir = is_dir($path);
    
    // Try to identify type
    $type = TypeManager::identifyType($name);
    
    $entry = [
      'name' => $name,
      'path' => $path,
      'isDir' => $isDir,
      'type' => $type
    ];
    
    // Load data from file
    if( $isDir )
    {
      $dataFile = $path . '/' . Config::get('dataFileName') . '.md';
      if( file_exists($dataFile) )
        $entry = array_merge($entry, self::parseDataFile($dataFile));
    }
    else
    {
      if( pathinfo($path, PATHINFO_EXTENSION) === 'md' )
        $entry = array_merge($entry, self::parseDataFile($path));
    }
    
    return $entry;
  }

  private static function parseDataFile( $filePath )
  {
    $content = file_get_contents($filePath);
    $data = [];
    
    // Parse front matter
    if( preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches) )
    {
      try {
        $frontMatter = Yaml::parse($matches[1]);
        if( is_array($frontMatter) )
          $data = $frontMatter;
      }
      catch( Exception $e ) {
        error_log("Failed to parse front matter in $filePath: " . $e->getMessage());
      }
      
      $data['description'] = trim($matches[2]);
    }
    else
    {
      $data['description'] = $content;
    }
    
    return $data;
  }

  public static function saveEntry( $path, $data )
  {
    $isDir = is_dir($path);
    
    if( $isDir )
    {
      $dataFile = $path . '/' . Config::get('dataFileName') . '.md';
    }
    else
    {
      $dataFile = $path;
      $dir = dirname($path);
      if( ! is_dir($dir) )
        mkdir($dir, 0755, true);
    }
    
    // Separate description from other data
    $description = $data['description'] ?? '';
    unset($data['description']);
    
    // Create front matter
    $frontMatter = '';
    if( ! empty($data) )
    {
      $frontMatter = "---\n" . Yaml::dump($data) . "---\n";
    }
    
    $content = $frontMatter . $description;
    
    if( file_put_contents($dataFile, $content) === false )
      throw new Exception("Failed to save data to $dataFile");
  }

  public static function deleteEntry( $path )
  {
    if( is_dir($path) )
    {
      self::deleteDirectory($path);
    }
    else
    {
      if( ! unlink($path) )
        throw new Exception("Failed to delete file $path");
    }
  }

  private static function deleteDirectory( $dir )
  {
    $items = scandir($dir);
    foreach( $items as $item )
    {
      if( $item === '.' || $item === '..' )
        continue;
        
      $fullPath = "$dir/$item";
      if( is_dir($fullPath) )
        self::deleteDirectory($fullPath);
      else
        unlink($fullPath);
    }
    
    rmdir($dir);
  }

  public static function getResources( $path )
  {
    $resources = [];
    
    if( ! is_dir($path) )
      return $resources;

    $items = scandir($path);
    foreach( $items as $item )
    {
      if( $item === '.' || $item === '..' )
        continue;
        
      $fullPath = "$path/$item";
      
      // Skip if it's an identified type instance
      $type = TypeManager::identifyType($item);
      if( $type )
        continue;
        
      // Skip the data file
      if( $item === Config::get('dataFileName') . '.md' )
        continue;
        
      $resources[] = [
        'name' => $item,
        'path' => $fullPath,
        'isDir' => is_dir($fullPath),
        'size' => is_file($fullPath) ? filesize($fullPath) : null
      ];
    }
    
    return $resources;
  }
}
