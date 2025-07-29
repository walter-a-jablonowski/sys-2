<?php

class InfoType
{
  public static function validateData( $data )
  {
    $errors = [];
    
    // Info type has no special fields, only basic validation
    // Name and description are handled by the global validation
    
    return $errors;
  }
  
  public static function processBeforeSave( $data )
  {
    // Info type doesn't need special processing
    return $data;
  }
  
  public static function getDisplayName( $name )
  {
    // Ensure Info entries have the (i) identifier
    if( ! preg_match('/.*\(\s*i\s*\).*/', $name) )
      return "$name (i)";
      
    return $name;
  }
}
