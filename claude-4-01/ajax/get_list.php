<?php
/**
 * Handle getting list data - common for all types
 */
function handleGetList( $data, $dataManager )
{
  $path = $data['path'] ?? '';
  
  // Load data for the current path
  $currentData = $dataManager->loadData($path);
  
  // Render the list
  $listHtml = $dataManager->renderList($currentData['entries'] ?? []);
  $resourcesHtml = $dataManager->renderResources($currentData['resources'] ?? []);
  
  return [
    'listHtml' => $listHtml,
    'resourcesHtml' => $resourcesHtml,
    'levelName' => $currentData['levelName'] ?? 'Start'
  ];
}
?>
