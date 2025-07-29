<?php

class ApartmentType
{
  public static function validateData( $data )
  {
    $errors = [];
    
    // Validate priority (inherited from Activity)
    if( isset($data['priority']) )
    {
      $priority = (int)$data['priority'];
      if( $priority < 1 || $priority > 5 )
        $errors[] = 'Priority must be between 1 and 5';
    }
    
    // Validate state (different from Activity)
    if( isset($data['state']) )
    {
      $validStates = ['new', 'current', 'maybe', 'done'];
      if( ! in_array($data['state'], $validStates) )
        $errors[] = 'State must be one of: new, current, maybe, done';
    }
    
    // Validate files_nr format
    if( isset($data['files_nr']) )
    {
      if( ! preg_match('/^\d{4}$/', $data['files_nr']) )
        $errors[] = 'Files number must be 4 digits with leading zeros';
    }
    
    // Validate URL format
    if( isset($data['url']) && ! empty($data['url']) )
    {
      if( ! filter_var($data['url'], FILTER_VALIDATE_URL) )
        $errors[] = 'URL must be a valid URL format';
    }
    
    // Validate due date format
    if( isset($data['dueDate']) && ! empty($data['dueDate']) )
    {
      if( ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['dueDate']) )
        $errors[] = 'Due date must be in format YYYY-MM-DD HH:MM:SS';
    }
    
    return $errors;
  }
  
  public static function processBeforeSave( $data )
  {
    // Ensure priority is integer
    if( isset($data['priority']) )
      $data['priority'] = (int)$data['priority'];
      
    // Set default state if not provided
    if( ! isset($data['state']) )
      $data['state'] = 'new';
      
    // Generate files_nr if not provided
    if( ! isset($data['files_nr']) )
      $data['files_nr'] = self::getNextFilesNr();
      
    return $data;
  }
  
  public static function getNextFilesNr()
  {
    $filesNrFile = 'user/Default/types/Apartment/files_nr.json';
    $dir = dirname($filesNrFile);
    
    if( ! is_dir($dir) )
      mkdir($dir, 0755, true);
      
    $currentNr = 1;
    if( file_exists($filesNrFile) )
    {
      $data = json_decode(file_get_contents($filesNrFile), true);
      $currentNr = ($data['last_nr'] ?? 0) + 1;
    }
    
    $data = ['last_nr' => $currentNr];
    file_put_contents($filesNrFile, json_encode($data));
    
    return str_pad($currentNr, 4, '0', STR_PAD_LEFT);
  }
  
  public static function getClosedDatePrefix( $date )
  {
    // Convert date to YYMMDD format for closed apartments
    return date('ymd', strtotime($date));
  }
  
  public static function shouldUseClosedNaming( $data )
  {
    return isset($data['state']) && $data['state'] === 'done';
  }
}
