<?php

/**
 * Creates a link stub in a container, pointing at an existing object.
 *
 * The stub carries only type and target: its label is its own file name
 * (plan §2.4). The target is given by path, a folder or a resource without
 * id gets one now: the stub can point at nothing else.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;

$parent = $ajax->obj('parentPath');
$target = $app->index->ensureId($ajax->obj('targetPath'));

if( $target->id === '' )
  throw Failure::invalid("\"{$target->title}\" cannot be linked to");

$link = $app->store->createLink($parent->path, $target->title, $target->id);

$app->index->invalidate();

// A link is drawn by its target's renderer, so the target has to be attached
$link->linkTarget = $target;

return [
  'path' => $link->path,
  'card' => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $link)->html()
];
