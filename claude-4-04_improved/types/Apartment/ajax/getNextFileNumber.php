<?php
try 
{
  $counterFile = 'types/Apartment/files_nr.json';
  
  // Initialize counter if file doesn't exist
  if( ! file_exists($counterFile) )
  {
    $counter = ['current' => 0];
    file_put_contents($counterFile, json_encode($counter));
  }
  else
  {
    $counter = json_decode(file_get_contents($counterFile), true);
  }
  
  // Increment counter
  $counter['current']++;
  
  // Save updated counter
  file_put_contents($counterFile, json_encode($counter));
  
  // Format as 4-digit string with leading zeros
  $files_nr = str_pad($counter['current'], 4, '0', STR_PAD_LEFT);
  
  echo json_encode([
    'success' => true,
    'files_nr' => $files_nr
  ]);
}
catch( Exception $e )
{
  throw new Exception('Failed to generate file number: ' . $e->getMessage());
}
