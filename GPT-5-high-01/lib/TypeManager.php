<?php

use Symfony\Component\Yaml\Yaml;

class TypeManager
{
  private static $types = null; // id => def

  public static function load() : array
  {
    if( self::$types !== null ) return self::$types;

    $types = [];
    $base = 'types';

    // Global defaults are implicit (name, time, description) and not required to read from file
    // But we still load /types/def.yml if present to allow future extensions
    $global = [];
    if( file_exists($base . '/def.yml') )
    {
      $global = Yaml::parse(file_get_contents($base . '/def.yml')) ?? [];
    }

    // Enumerate subfolders in /types
    if( is_dir($base) )
    {
      foreach( scandir($base) as $entry )
      {
        if( $entry === '.' || $entry === '..' || $entry === 'def.yml' ) continue;
        $dir = "$base/$entry";
        if( ! is_dir($dir) ) continue;
        $defPath = "$dir/def.yml";
        if( ! file_exists($defPath) ) continue;
        $def = Yaml::parse(file_get_contents($defPath)) ?? [];
        if( ! isset($def['id']) || ! $def['id'] ) continue;
        $id = (string)$def['id'];
        $def['dir'] = $dir;
        $def['listRenderer'] = "$dir/list.php";
        $def['readOnlyRenderer'] = "$dir/read_only.php";
        $def['editRenderer'] = "$dir/edit.php";
        // Normalize fields structure
        if( ! isset($def['fields']) || ! is_array($def['fields']) ) $def['fields'] = [];
        // Normalize allowedSubTypes
        if( ! isset($def['allowedSubTypes']) ) $def['allowedSubTypes'] = [];
        self::applyInheritance($def, $types);
        $types[$id] = $def;
      }
    }

    self::$types = $types;
    return self::$types;
  }

  private static function applyInheritance( array &$def, array $existingTypes ) : void
  {
    if( ! isset($def['derivedFrom']) || ! $def['derivedFrom'] ) return;
    $baseId = (string)$def['derivedFrom'];
    if( isset($existingTypes[$baseId]) )
    {
      $parent = $existingTypes[$baseId];
      // Inherit fields
      $def['fields'] = array_merge($parent['fields'] ?? [], $def['fields'] ?? []);
      // Inherit allowedSubTypes (child can override completely if set)
      if( ! isset($def['allowedSubTypes']) || $def['allowedSubTypes'] === null || $def['allowedSubTypes'] === [] )
      {
        $def['allowedSubTypes'] = $parent['allowedSubTypes'] ?? [];
      }
    }
  }

  public static function getTypes() : array
  {
    return self::load();
  }

  public static function getType( string $id ) : ?array
  {
    $types = self::load();
    return $types[$id] ?? null;
  }

  public static function getTypesShort() : array
  {
    $out = [];
    foreach( self::load() as $id => $def )
    {
      $out[] = [ 'id' => $id, 'name' => $def['name'] ?? $id ];
    }
    return $out;
  }

  public static function identify( string $fileOrFolderName, array $frontMatter ) : ?string
  {
    // Front matter type wins
    if( isset($frontMatter['type']) )
    {
      $t = (string)$frontMatter['type'];
      if( self::getType($t) ) return $t;
    }

    $name = $fileOrFolderName;
    foreach( self::load() as $id => $def )
    {
      $patterns = $def['typeIdentification'] ?? null;
      if( $patterns === null ) continue;
      if( is_string($patterns) ) $patterns = [ $patterns ];
      if( ! is_array($patterns) ) continue;
      foreach( $patterns as $rx )
      {
        $rx = self::rx($rx);
        if( preg_match($rx, $name) ) return $id;
      }
    }
    return null;
  }

  private static function rx( string $raw ) : string
  {
    // Raw should come as already escaped; add delimiters and i flag
    return '/' . str_replace('/', '\/', $raw) . '/i';
  }

  public static function defaultInstanceData( string $typeId ) : array
  {
    $data = [
      'time' => Util::now(),
      'name' => '',
      'description' => ''
    ];
    $t = self::getType($typeId);
    if( $t )
    {
      foreach( $t['fields'] as $fname => $fdef )
      {
        if( ! isset($data[$fname]) )
        {
          $data[$fname] = self::defaultValueFor($fdef);
        }
      }
      // For derived types keep parent's defaults already handled by merged fields
    }
    return $data;
  }

  private static function defaultValueFor( array $fdef )
  {
    $type = $fdef['type'] ?? 'string';
    if( $type === 'int' || $type === 'float' ) return 0;
    if( $type === 'bool' ) return false;
    return '';
  }

  public static function buildDisplayName( string $typeId, array $data ) : string
  {
    $name = trim((string)($data['name'] ?? ''));
    if( $typeId === 'Info' )
    {
      return '(i) ' . $name;
    }
    if( $typeId === 'Activity' || $typeId === 'Apartment' )
    {
      $state = (string)($data['state'] ?? 'new');
      if( strtolower($state) === 'done' )
      {
        $dd = date('ymd');
        return "$dd - $name";
      }
      $prio = (string)($data['priority'] ?? '3');
      $prio = preg_match('/^[1-5]$/', $prio) ? $prio : '3';
      return "$prio - $name";
    }
    return $name === '' ? 'item' : $name;
  }

  public static function validate( string $typeId, array $data ) : array
  {
    $errors = [];
    if( trim((string)($data['name'] ?? '')) === '' )
    {
      $errors['name'] = 'Name is required';
    }
    $t = self::getType($typeId);
    if( $t )
    {
      foreach( $t['fields'] as $fname => $fdef )
      {
        $required = (bool)($fdef['required'] ?? false);
        if( $required && (! isset($data[$fname]) || $data[$fname] === '' || $data[$fname] === null) )
        {
          $errors[$fname] = 'Required';
          continue;
        }
        if( isset($data[$fname]) && $data[$fname] !== '' )
        {
          $type = $fdef['type'] ?? 'string';
          if( $type === 'int' && ! preg_match('/^-?\d+$/', (string)$data[$fname]) )
          {
            $errors[$fname] = 'Must be integer';
          }
          if( $type === 'float' && ! is_numeric($data[$fname]) )
          {
            $errors[$fname] = 'Must be number';
          }
          if( $type === 'string' && isset($fdef['format']) )
          {
            $rx = '/' . str_replace('/', '\/', (string)$fdef['format']) . '/';
            if( ! preg_match($rx, (string)$data[$fname]) )
            {
              $errors[$fname] = 'Invalid format';
            }
          }
          if( $type === 'bool' )
          {
            $v = $data[$fname];
            if( ! is_bool($v) && ! in_array($v, ['0','1',0,1], true) )
            {
              $errors[$fname] = 'Must be boolean';
            }
          }
        }
      }
    }
    return $errors;
  }
}
