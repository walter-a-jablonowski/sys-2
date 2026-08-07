<?php

/**
 * Type action behind the "Mark as done" menu entry.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

$obj  = $ajax->obj();
$data = $obj->data;

$data['status'] = 'done';

$saved = $app->store->save($obj, $data, $obj->body, null);

return [
  'path'    => $saved->path,
  'card'    => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $saved)->html(),
  'message' => "'{$saved->title}' marked as done"
];
