<?php

/**
 * Hard delete, no trash (plan §2.10). The confirm dialog is built from the
 * numbers this handler reports when it is asked without "confirmed".
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

$obj      = $ajax->obj();
$children = $obj->isFolder ? $app->store->countChildren($obj->path) : 0;
$links    = $obj->id === '' ? [] : $app->index->backlinks($obj->id);

if( ! $ajax->param('confirmed', ''))
{
  return [
    'confirm'  => true,
    'path'     => $obj->path,
    'title'    => $obj->title,
    'isFolder' => $obj->isFolder,
    'children' => $children,
    'links'    => $links
  ];
}

$app->store->delete($obj);
$app->index->invalidate();

return [
  'deleted' => $obj->path,
  'parent'  => $obj->parentPath(),
  'links'   => $links
];
