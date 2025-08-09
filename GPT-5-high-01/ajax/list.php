<?php

$path = isset($_GET['path']) ? (string)$_GET['path'] : '';

$res = DataManager::listAt($path);

$current = $res['current'];
$readOnlyHtml = '';
if( $current && $current['type'] )
{
  $readOnlyHtml = DataManager::renderReadOnly($current['type'], $current['data']);
}

$items = [];
foreach( $res['typed'] as $item )
{
  $items[] = [
    'rel' => $item['rel'],
    'type' => $item['type'],
    'name' => $item['name'],
    'data' => $item['data'],
    'cellHtml' => DataManager::renderListCell($item['type'], $item['data'])
  ];
}

$resources = [];
foreach( $res['resources'] as $r )
{
  $resources[] = [
    'rel' => $r['rel'],
    'name' => $r['name'],
    'isDir' => $r['isDir'],
    'size' => $r['size'],
    'sizeNice' => $r['isDir'] ? '' : Util::fileSizeNice((int)$r['size']),
    'mtime' => $r['mtime']
  ];
}

// Compute allowed types (filter by current type's allowedSubTypes if present)
$typesShort = TypeManager::getTypesShort();
$allowed = '*';
if( $current && isset($current['type']) && $current['type'] )
{
  $tdef = TypeManager::getType($current['type']);
  if( $tdef ) $allowed = $tdef['allowedSubTypes'] ?? '*';
}
$types = [];
if( $allowed === '*' )
{
  $types = $typesShort;
}
else
{
  $allowed = (array)$allowed;
  foreach( $typesShort as $t )
  {
    if( in_array($t['id'], $allowed, true) ) $types[] = $t;
  }
}

echo json_encode([
  'ok' => true,
  'current' => $current,
  'readOnlyHtml' => $readOnlyHtml,
  'items' => $items,
  'resources' => $resources,
  'types' => $types
]);
