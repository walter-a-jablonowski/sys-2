<?php
// Debug script to test data loading

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'core/functions.php';
require_once 'core/type_manager.php';
require_once 'core/data_manager.php';

echo "<h1>Debug Information</h1>";

// Test TypeManager
echo "<h2>Type Manager Test</h2>";
$typeManager = new TypeManager();
$types = $typeManager->getAllTypes();

echo "<h3>Loaded Types:</h3>";
foreach( $types as $typeId => $typeDef ) {
  echo "<p><strong>$typeId:</strong><br>";
  echo "Name: " . ($typeDef['name'] ?? 'N/A') . "<br>";
  echo "TypeIdentification: " . ($typeDef['typeIdentification'] ?? 'N/A') . "<br>";
  echo "AllowedSubTypes: " . print_r($typeDef['allowedSubTypes'] ?? [], true) . "</p>";
}

// Test type identification
echo "<h3>Type Identification Tests:</h3>";
$testNames = [
  '2 - MyApartmentSearch',
  '3 - SunnyApartment', 
  'WelcomeInfo (i)',
  'ViewingNotes (i)'
];

foreach( $testNames as $name ) {
  $type = $typeManager->identifyType($name);
  echo "<p>Name: '$name' -> Type: " . ($type ?? 'NULL') . "</p>";
}

// Test DataManager
echo "<h2>Data Manager Test</h2>";
$dataManager = new DataManager();

echo "<h3>Current Level Data (root):</h3>";
$currentData = $dataManager->getCurrentLevelData('');
echo "<pre>";
print_r($currentData);
echo "</pre>";

// Test specific paths
echo "<h3>Type Identification from Path:</h3>";
$testPaths = [
  'data/2 - MyApartmentSearch',
  'data/WelcomeInfo (i)',
  'data/2 - MyApartmentSearch/3 - SunnyApartment'
];

foreach( $testPaths as $path ) {
  if( file_exists($path) ) {
    $type = $typeManager->identifyTypeFromPath($path);
    echo "<p>Path: '$path' -> Type: " . ($type ?? 'NULL') . "</p>";
  } else {
    echo "<p>Path: '$path' -> FILE NOT FOUND</p>";
  }
}
?>
