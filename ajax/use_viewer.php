<?php

/**
 * Makes a viewer the one the user sees a type with.
 *
 * Answers with the viewer's assets, so the page can swap the stylesheet of
 * the type and load its script without a reload.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;

$typeName = (string) $ajax->param('type');

if( ! $app->types->has($typeName))
  throw Failure::invalid("Unknown type: $typeName");

$viewer = $app->viewers->choose($app->types->get($typeName), (string) $ajax->param('viewer'));

return [
  'type'   => $typeName,
  'viewer' => $viewer->name,
  'styles' => $viewer->hasStyles() ? $app->asset("{$viewer->path}/styles.css") : '',
  'script' => $viewer->hasScript() ? $app->asset("{$viewer->path}/controller.js") : ''
];
