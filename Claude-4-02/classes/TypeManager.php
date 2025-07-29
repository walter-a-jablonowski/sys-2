<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

class TypeManager 
{
  private $types = [];
  private $globalFields = [];

  public function __construct() 
  {
    $this->loadGlobalFields();
    $this->loadTypes();
  }

  private function loadGlobalFields() 
  {
    if( file_exists('types/def.yml') ) {
      $this->globalFields = Yaml::parseFile('types/def.yml');
    }
  }

  private function loadTypes() 
  {
    $typeDirs = glob('types/*', GLOB_ONLYDIR);
    
    foreach( $typeDirs as $typeDir ) {
      $typeName = basename($typeDir);
      $defFile = "$typeDir/def.yml";
      
      if( file_exists($defFile) ) {
        $typeDef = Yaml::parseFile($defFile);
        $this->types[$typeName] = $typeDef;
      }
    }
  }

  public function getType( $typeName ) 
  {
    return $this->types[$typeName] ?? null;
  }

  public function getAllTypes() 
  {
    return $this->types;
  }

  public function getGlobalFields() 
  {
    return $this->globalFields;
  }

  public function identifyType( $name ) 
  {
    foreach( $this->types as $typeName => $typeDef ) {
      if( isset($typeDef['typeIdentification']) && ! empty($typeDef['typeIdentification']) ) {
        if( preg_match('/' . $typeDef['typeIdentification'] . '/', $name) ) {
          return $typeName;
        }
      }
    }
    return null;
  }

  public function getAllowedSubTypes( $typeName ) 
  {
    $type = $this->getType($typeName);
    if( ! $type ) {
      return [];
    }

    $allowedSubTypes = $type['allowedSubTypes'] ?? [];
    
    if( in_array('*', $allowedSubTypes) ) {
      return array_keys($this->types);
    }
    
    return $allowedSubTypes;
  }

  public function generateId( $name ) 
  {
    // Convert each word to first character uppercase
    $words = explode(' ', $name);
    $id = '';
    
    foreach( $words as $word ) {
      if( ! empty($word) ) {
        $id .= strtoupper(substr($word, 0, 1)) . substr($word, 1);
      }
    }
    
    // Remove all non alphanumeric chars
    $id = preg_replace('/[^a-zA-Z0-9]/', '', $id);
    
    // Add unique user and date
    $timestamp = date('ymdHis');
    $id .= "-Default-$timestamp";
    
    return $id;
  }

  public function getNextFilesNr() 
  {
    $jsonFile = 'types/Apartment/files_nr.json';
    $data = ['last_id' => 0];
    
    if( file_exists($jsonFile) ) {
      $content = file_get_contents($jsonFile);
      $data = json_decode($content, true) ?: $data;
    }
    
    $data['last_id']++;
    file_put_contents($jsonFile, json_encode($data));
    
    return str_pad($data['last_id'], 4, '0', STR_PAD_LEFT);
  }
}
