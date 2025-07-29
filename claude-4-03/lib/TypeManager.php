<?php

require_once 'Config.php';

use Symfony\Component\Yaml\Yaml;

class TypeManager
{
  private static $types = null;
  private static $globalDef = null;

  public static function loadTypes()
  {
    if( self::$types !== null )
      return;

    self::$types = [];
    
    // Load global definition
    try {
      self::$globalDef = Yaml::parseFile('types/def.yml');
    }
    catch( Exception $e ) {
      throw new Exception("Failed to load types/def.yml: " . $e->getMessage());
    }

    // Load all type definitions
    $typesDirs = glob('types/*', GLOB_ONLYDIR);
    foreach( $typesDirs as $typeDir )
    {
      $typeName = basename($typeDir);
      $defFile = "$typeDir/def.yml";
      
      if( file_exists($defFile) )
      {
        try {
          $typeDef = Yaml::parseFile($defFile);
          self::$types[$typeName] = $typeDef;
        }
        catch( Exception $e ) {
          error_log("Failed to load type definition for $typeName: " . $e->getMessage());
        }
      }
    }
  }

  public static function getType( $typeId )
  {
    self::loadTypes();
    return self::$types[$typeId] ?? null;
  }

  public static function getAllTypes()
  {
    self::loadTypes();
    return self::$types;
  }

  public static function getGlobalDef()
  {
    self::loadTypes();
    return self::$globalDef;
  }

  public static function identifyType( $name )
  {
    self::loadTypes();
    
    foreach( self::$types as $typeId => $typeDef )
    {
      if( isset($typeDef['typeIdentification']) )
      {
        $patterns = is_array($typeDef['typeIdentification']) 
          ? $typeDef['typeIdentification'] 
          : [$typeDef['typeIdentification']];
          
        foreach( $patterns as $pattern )
        {
          if( preg_match("/$pattern/", $name) )
            return $typeId;
        }
      }
    }
    
    return null;
  }

  public static function generateId( $name, $user = 'Default' )
  {
    // Convert each word to first character uppercase
    $words = preg_split('/\s+/', trim($name));
    $id = '';
    foreach( $words as $word )
    {
      if( ! empty($word) )
        $id .= ucfirst(strtolower($word));
    }
    
    // Remove all non-alphanumeric chars
    $id = preg_replace('/[^a-zA-Z0-9]/', '', $id);
    
    // Add user and date
    $date = date('ymdHis');
    return "$id-$user-$date";
  }
}
