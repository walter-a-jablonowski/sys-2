<?php

/**
 * Pins or unpins an object, whichever way round it was.
 *
 * A link stub pins its target: opening a link opens the target, and Recent
 * keeps the target as well. A stub has no id of its own, it points at one.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;

$obj = $ajax->obj();

if( $obj->isLink )
  $obj = $app->index->target($obj) ?? throw Failure::invalid('A broken link has nothing to pin');

// Pinned keeps ids: a folder or a resource without one gets it now
$obj = $app->index->ensureId($obj);

return [
  'path'   => $obj->path,
  'pinned' => $app->pinned->toggle($obj->id)
];
