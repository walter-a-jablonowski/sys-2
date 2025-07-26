<?php
// Type management class

class TypeManager
{
  private $types = [];
  private $globalDef = [];
  
  public function __construct()
  {
    $this->loadTypes();
  }
  
  /**
   * Load all type definitions
   */
  private function loadTypes()
  {
    // Load global definition
    $globalDefPath = 'types/def.yml';
    if( file_exists($globalDefPath) ) {
      $this->globalDef = $this->parseYamlFile($globalDefPath);
    }
    
    // Load individual type definitions
    if( is_dir('types') ) {
      $typeDirs = scandir('types');
      
      foreach( $typeDirs as $dir ) {
        if( $dir === '.' || $dir === '..' || $dir === 'def.yml' ) {
          continue;
        }
        
        $typePath = "types/$dir";
        if( is_dir($typePath) ) {
          $defFile = "$typePath/def.yml";
          if( file_exists($defFile) ) {
            $typeDef = $this->parseYamlFile($defFile);
            $this->types[$dir] = $typeDef;
          }
        }
      }
    }
  }
  
  /**
   * Parse YAML file (simple implementation)
   */
  private function parseYamlFile( $filepath )
  {
    if( !file_exists($filepath) ) {
      return [];
    }
    
    $content = file_get_contents($filepath);
    $lines = explode("\n", $content);
    $data = [];
    $currentKey = null;
    $multilineValue = '';
    $inMultiline = false;
    
    foreach( $lines as $line ) {
      $line = rtrim($line);
      
      // Handle multiline values (|)
      if( $inMultiline ) {
        if( empty($line) || strpos($line, ' ') === 0 ) {
          $multilineValue .= ($multilineValue ? "\n" : '') . ltrim($line);
          continue;
        } else {
          $data[$currentKey] = $multilineValue;
          $inMultiline = false;
          $multilineValue = '';
        }
      }
      
      if( empty($line) || strpos($line, '#') === 0 ) {
        continue;
      }
      
      if( strpos($line, ':') !== false ) {
        list($key, $value) = explode(':', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Handle multiline indicator
        if( $value === '|' ) {
          $currentKey = $key;
          $inMultiline = true;
          $multilineValue = '';
          continue;
        }
        
        // Remove comments
        if( strpos($value, '#') !== false ) {
          $value = trim(explode('#', $value)[0]);
        }
        
        // Remove quo
        if( (substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'") ) {
          $value = substr($value, 1, -1);
        }
        
        // Handle arrays
        if( substr($value, 0, 1) === '[' && substr($value, -1) === ']' ) {
          $value = substr($value, 1, -1);
          $value = array_map('trim', explode(',', $value));
          $value = array_map(function($v) {
            return trim($v, '"\'');
          }, $value);
        }
        
        $data[$key] = $value;
      }
    }
    
    // Handle final multiline value
    if( $inMultiline ) {
      $data[$currentKey] = $multilineValue;
    }
    
    return $data;
  }
  
  /**
   * Check if type exists
   */
  public function typeExists( $typeId )
  {
    return isset($this->types[$typeId]);
  }
  
  /**
   * Get type definition
   */
  public function getType( $typeId )
  {
    return $this->types[$typeId] ?? null;
  }
  
  /**
   * Get all types
   */
  public function getAllTypes()
  {
    return $this->types;
  }
  
  /**
   * Check if type is allowed at current path
   */
  public function isTypeAllowed( $typeId, $currentPath )
  {
    if( empty($currentPath) ) {
      return true;  // all types allowed in start page
    }
    
    // Get parent instance type
    $parentInstance = $this->getInstanceAtPath($currentPath);
    if( !$parentInstance ) {
      return true;
    }
    
    $parentType = $this->getType($parentInstance['type']);
    if( !$parentType ) {
      return true;
    }
    
    $allowedSubTypes = $parentType['allowedSubTypes'] ?? [];
    
    // Handle special cases
    if( $allowedSubTypes === '*' || $allowedSubTypes === 'all' || 
        (is_array($allowedSubTypes) && in_array('*', $allowedSubTypes)) ) {
      return true;
    }
    
    if( empty($allowedSubTypes) ) {
      return false;
    }
    
    return is_array($allowedSubTypes) && in_array($typeId, $allowedSubTypes);
  }
  
  /**
   * Get instance at path (helper method)
   */
  private function getInstanceAtPath( $path )
  {
    $dataFile = "data/$path/-this.md";
    if( file_exists($dataFile) ) {
      $content = file_get_contents($dataFile);
      $parsed = parseFrontMatter($content);
      return $parsed['data'];
    }
    return null;
  }
  
  /**
   * Get type options for dropdown
   */
  public function getTypeOptions( $currentPath )
  {
    $options = '';
    
    foreach( $this->types as $typeId => $typeDef ) {
      if( $this->isTypeAllowed($typeId, $currentPath) ) {
        $name = $typeDef['name'] ?? $typeId;
        $options .= "<option value=\"$typeId\">$name</option>\n";
      }
    }
    
    return $options;
  }
  
  /**
   * Identify type from filename/path
   */
  public function identifyType( $name )
  {
    foreach( $this->types as $typeId => $typeDef ) {
      $pattern = $typeDef['typeIdentification'] ?? '';
      if( !empty($pattern) && preg_match("/".$pattern."/", $name) ) {
        return $typeId;
      }
    }
    return null;
  }
  
  /**
   * Identify type from file path, with fallback to front matter
   */
  public function identifyTypeFromPath( $itemPath )
  {
    $name = basename($itemPath);
    
    // First try regex identification
    $type = $this->identifyType($name);
    if( $type ) {
      return $type;
    }
    
    // Fallback: try to read type from front matter
    if( is_dir($itemPath) ) {
      $dataFile = "$itemPath/-this.md";
    } else {
      $dataFile = $itemPath;
    }
    
    if( file_exists($dataFile) ) {
      $content = file_get_contents($dataFile);
      $parsed = parseFrontMatter($content);
      
      if( isset($parsed['data']['type']) ) {
        $frontMatterType = $parsed['data']['type'];
        // Verify this type exists
        if( $this->typeExists($frontMatterType) ) {
          return $frontMatterType;
        }
      }
    }
    
    return null;
  }
  
  /**
   * Get default fields for all types
   */
  public function getDefaultFields()
  {
    return [
      'time' => getCurrentTimestamp(),
      'name' => '',
      'description' => '',
      'id' => ''
    ];
  }
  
  /**
   * Get fields for specific type
   */
  public function getTypeFields( $typeId )
  {
    $type = $this->getType($typeId);
    if( !$type ) {
      return [];
    }
    
    return $type['fields'] ?? [];
  }
  
  /**
   * Validate field value
   */
  public function validateField( $fieldDef, $value )
  {
    if( !is_array($fieldDef) ) {
      return true; // No validation rules
    }
    
    // Required check
    if( ($fieldDef['required'] ?? false) && empty($value) ) {
      throw new Exception("Field is required");
    }
    
    // Type validation
    $type = $fieldDef['type'] ?? 'string';
    
    switch( $type ) {
      case 'int':
        if( !is_numeric($value) || (int)$value != $value ) {
          throw new Exception("Value must be an integer");
        }
        
        $intValue = (int)$value;
        if( isset($fieldDef['min']) && $intValue < $fieldDef['min'] ) {
          throw new Exception("Value must be at least {$fieldDef['min']}");
        }
        if( isset($fieldDef['max']) && $intValue > $fieldDef['max'] ) {
          throw new Exception("Value must be at most {$fieldDef['max']}");
        }
        break;
        
      case 'float':
        if( !is_numeric($value) ) {
          throw new Exception("Value must be a number");
        }
        
        $floatValue = (float)$value;
        if( isset($fieldDef['min']) && $floatValue < $fieldDef['min'] ) {
          throw new Exception("Value must be at least {$fieldDef['min']}");
        }
        if( isset($fieldDef['max']) && $floatValue > $fieldDef['max'] ) {
          throw new Exception("Value must be at most {$fieldDef['max']}");
        }
        break;
        
      case 'bool':
        if( !in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no']) ) {
          throw new Exception("Value must be true or false");
        }
        break;
        
      case 'string':
        if( isset($fieldDef['format']) && !preg_match("/{$fieldDef['format']}/", $value) ) {
          throw new Exception("Value format is invalid");
        }
        break;
        
      case 'hyperlink':
        if( !empty($value) && !filter_var($value, FILTER_VALIDATE_URL) ) {
          throw new Exception("Value must be a valid URL");
        }
        break;
    }
    
    return true;
  }
}
?>
