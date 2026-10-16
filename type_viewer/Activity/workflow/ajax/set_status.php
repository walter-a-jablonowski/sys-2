<?php

/**
 * Moves an activity to another status, behind the workflow toolbar.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;

$obj    = $ajax->obj();
$status = (string) $ajax->param('status');
$states = $app->types->forObj($obj)->fields()['status']['options'] ?? [];

if( ! in_array($status, $states, true))
  throw Failure::invalid("Unknown status: $status");

$data = $obj->data;

$data['status'] = $status;

$saved = $app->store->save($obj, $data, $obj->body, null);

return [
  'path'    => $saved->path,
  'card'    => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $saved)->html(),
  'message' => "'{$saved->title}' is $status now"
];
