<?php

/**
 * Creates a link stub in a container, pointing at an existing object.
 *
 * The stub carries only type and target: its label is its own file name
 * (plan §2.4).
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;

$parent = $ajax->obj('parentPath');
$id     = (string) $ajax->param('targetId', '');
$target = $app->index->obj($id);

if( $target === null )
  throw Failure::notFound("target: $id");

$link = $app->store->createLink($parent->path, $target->title, $id);

$app->index->invalidate();

// A link is drawn by its target's renderer, so the target has to be attached
$link->linkTarget = $target;

return [
  'path' => $link->path,
  'card' => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $link)->html()
];
