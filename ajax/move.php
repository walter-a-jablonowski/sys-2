<?php

/**
 * Moves an object into another container. The id never changes, so every
 * link keeps working.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

$obj    = $ajax->obj();
$target = (string) $ajax->param('targetPath', '');
$moved  = $app->store->move($obj, $target);

$app->index->invalidate();

return [
  'path'   => $moved->path,
  'parent' => $moved->parentPath(),
  'from'   => $obj->path
];
