<?php

/**
 * Utility functions for the application
 */

// Include Composer autoloader
require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

// Function to load YAML file
function loadYaml($file) {
  if( !file_exists($file) ) {
    return [];
  }
  
  return Yaml::parseFile($file);
}

// Function to load all type definitions
function loadTypeDefinitions() {
  $types = [];
  
  // Load global definition
  $globalDef = loadYaml('types/def.yml');
  
  // Load each type definition
  $typeDirs = array_filter(glob('types/*'), 'is_dir');
  
  foreach( $typeDirs as $typeDir ) {
    $typeName = basename($typeDir);
    $typeDefFile = $typeDir . '/def.yml';
    
    if( file_exists($typeDefFile) ) {
      $typeDef = loadYaml($typeDefFile);
      
      // Merge with global definition
      if( !isset($typeDef['fields']) ) {
        $typeDef['fields'] = [];
      }
      
      // Add global fields
      if( isset($globalDef['time']) ) $typeDef['fields']['time'] = $globalDef['time'];
      if( isset($globalDef['name']) ) $typeDef['fields']['name'] = $globalDef['name'];
      if( isset($globalDef['description']) ) $typeDef['fields']['description'] = $globalDef['description'];
      
      $types[$typeName] = $typeDef;
    }
  }
  
  return $types;
}

// Function to identify type of a file/folder
function identifyType($name, $types) {
  foreach( $types as $typeName => $typeDef ) {
    if( isset($typeDef['typeIdentification']) ) {
      $patterns = is_array($typeDef['typeIdentification']) ? $typeDef['typeIdentification'] : [$typeDef['typeIdentification']];
      
      foreach( $patterns as $pattern ) {
        if( preg_match('/' . $pattern . '/', $name) ) {
          return $typeName;
        }
      }
    }
  }
  
  return null;
}

// Function to parse front matter from markdown file
function parseFrontMatter($content) {
  $data = [];
  $lines = explode("\n", $content);
  $inFrontMatter = false;
  $frontMatterLines = [];
  
  foreach( $lines as $line ) {
    if( trim($line) === '---' ) {
      $inFrontMatter = !$inFrontMatter;
      if( !$inFrontMatter ) {
        // Parse front matter
        $yaml = implode("\n", $frontMatterLines);
        $data = Yaml::parse($yaml);
        break;
      }
      continue;
    }
    
    if( $inFrontMatter ) {
      $frontMatterLines[] = $line;
    }
  }
  
  return $data;
}

// Function to generate ID from name
function generateId($name) {
  // Convert each word to first character uppercase
  $idName = preg_replace_callback('/\b\w/', function($matches) {
    return strtoupper($matches[0]);
  }, $name);
  
  // Remove all non-alphanumeric characters
  $idName = preg_replace('/[^a-zA-Z0-9]/', '', $idName);
  
  // Add unique user and date
  $now = new DateTime();
  $dateStr = $now->format('ymdHis');
  
  return $idName . '-Default-' . $dateStr;
}

?>
