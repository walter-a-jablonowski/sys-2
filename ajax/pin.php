<?php

/**
 * Pins or unpins an object, whichever way round it was.
 *
 * A link stub is pinned as itself, not as its target: pinning means keeping
 * a place in the hierarchy, and the stub is that place.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

// Pinned keeps ids: a folder or a resource without one gets it now
$obj = $app->index->ensureId($ajax->obj());

return [
  'path'   => $obj->path,
  'pinned' => $app->pinned->toggle($obj->id)
];
