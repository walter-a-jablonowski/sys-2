<?php

/**
 * Creates an object below a container and opens it in the editor.
 *
 * The name is a placeholder ("New Activity"): the title is the file name, so
 * an object needs one before it can exist (plan §2.9).
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;

$parent   = $ajax->obj('parentPath');
$typeName = (string) $ajax->param('type', '');

if( ! $app->types->has($typeName))
  throw Failure::invalid("Unknown type: $typeName");

$type = $app->types->get($typeName);
$name = $type->newName();
$obj  = $app->store->create($parent->path, $typeName, $name, $type->newId($name));

$app->index->invalidate();

return [
  'path'  => $obj->path,
  'title' => $obj->title,
  'card'  => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $obj)->html()
];
