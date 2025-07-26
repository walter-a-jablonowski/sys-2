<?php
// Core utility functions

/**
 * Generate a unique ID from name following the specified format
 * Format: convert to title case, remove non-alphanumeric, add user and date
 */
function generateId( $name )
{
  // Convert each word to first character uppercase
  $words = explode(' ', trim($name));
  $titleCase = '';
  
  foreach( $words as $word ) {
    if( !empty($word) ) {
      $titleCase .= ucfirst(strtolower($word));
    }
  }
  
  // Remove all non-alphanumeric characters
  $clean = preg_replace('/[^a-zA-Z0-9]/', '', $titleCase);
  
  // Add user and date (YYMMDDHHMMSS format)
  $dateStr = date('ymdHis');
  $user = 'Default';
  
  return $clean . '-' . $user . '-' . $dateStr;
}

/**
 * Get current timestamp in YYYY-MM-DD HH:MM:SS format
 */
function getCurrentTimestamp()
{
  return date('Y-m-d H:i:s');
}

/**
 * Format date for display (MM-DD format)
 */
function formatDateShort( $timestamp )
{
  return date('m-d', strtotime($timestamp));
}

/**
 * Format date for display (YYYY-MM-DD format)
 */
function formatDateLong( $timestamp )
{
  return date('Y-m-d', strtotime($timestamp));
}

/**
 * Safely get array value with default
 */
function getValue( $array, $key, $default = '' )
{
  return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Validate required fields
 */
function validateRequired( $data, $requiredFields )
{
  $missing = [];
  
  foreach( $requiredFields as $field ) {
    if( !isset($data[$field]) || trim($data[$field]) === '' ) {
      $missing[] = $field;
    }
  }
  
  if( !empty($missing) ) {
    throw new Exception('Missing required fields: ' . implode(', ', $missing));
  }
}

/**
 * Sanitize string for filename use
 */
function sanitizeFilename( $name )
{
  // Replace spaces with hyphens and remove special characters
  $clean = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $name);
  $clean = preg_replace('/\s+/', '-', trim($clean));
  return strtolower($clean);
}

/**
 * Check if path exists and create if needed
 */
function ensureDirectoryExists( $path )
{
  if( !is_dir($path) ) {
    if( !mkdir($path, 0755, true) ) {
      throw new Exception("Failed to create directory: $path");
    }
  }
}

/**
 * Parse YAML front matter from markdown file
 */
function parseFrontMatter( $content )
{
  $pattern = '/^---\s*\n(.*?)\n---\s*\n(.*)$/s';
  
  if( preg_match($pattern, $content, $matches) ) {
    $yaml = $matches[1];
    $markdown = $matches[2];
    
    // Simple YAML parser for basic key: value pairs
    $data = [];
    $lines = explode("\n", $yaml);
    
    foreach( $lines as $line ) {
      $line = trim($line);
      if( empty($line) || strpos($line, ':') === false ) {
        continue;
      }
      
      list($key, $value) = explode(':', $line, 2);
      $key = trim($key);
      $value = trim($value);
      
      // Remove quotes if present
      if( (substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
          (substr($value, 0, 1) === "'" && substr($value, -1) === "'") ) {
        $value = substr($value, 1, -1);
      }
      
      $data[$key] = $value;
    }
    
    return ['data' => $data, 'content' => trim($markdown)];
  }
  
  return ['data' => [], 'content' => $content];
}

/**
 * Create YAML front matter content
 */
function createFrontMatter( $data, $content = '' )
{
  $yaml = "---\n";
  
  foreach( $data as $key => $value ) {
    // Escape quotes in values
    if( is_string($value) && (strpos($value, '"') !== false || strpos($value, "'") !== false) ) {
      $value = '"' . str_replace('"', '\\"', $value) . '"';
    }
    
    $yaml .= "$key: $value\n";
  }
  
  $yaml .= "---\n";
  
  if( !empty($content) ) {
    $yaml .= "\n" . $content;
  }
  
  return $yaml;
}

/**
 * Display error to user
 */
function displayError( $message, $details = '' )
{
  error_log("System Error: $message" . ($details ? " - Details: $details" : ""));
  
  // In production, you might want to show a generic message
  return htmlspecialchars($message);
}

/**
 * Log debug information
 */
function debugLog( $message, $data = null )
{
  $logMessage = "[DEBUG] $message";
  
  if( $data !== null ) {
    $logMessage .= " - Data: " . print_r($data, true);
  }
  
  error_log($logMessage);
}
?>
