<?php

class ActivityType
{
  public static function validateData( $data )
  {
    $errors = [];
    
    // Validate priority
    if( isset($data['priority']) )
    {
      $priority = (int)$data['priority'];
      if( $priority < 1 || $priority > 5 )
        $errors[] = 'Priority must be between 1 and 5';
    }
    
    // Validate state
    if( isset($data['state']) )
    {
      $validStates = ['new', 'progress', 'done'];
      if( ! in_array($data['state'], $validStates) )
        $errors[] = 'State must be one of: new, progress, done';
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
      
    return $data;
  }
  
  public static function getClosedDatePrefix( $date )
  {
    // Convert date to YYMMDD format for closed activities
    return date('ymd', strtotime($date));
  }
  
  public static function shouldUseClosedNaming( $data )
  {
    return isset($data['state']) && $data['state'] === 'done';
  }
}
