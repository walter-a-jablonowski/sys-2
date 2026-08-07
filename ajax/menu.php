<?php

/**
 * Entries of the "..." dropdown for one object.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Menu;

$obj = $ajax->obj();

return [
  'path'    => $obj->path,
  'title'   => $obj->title,
  'type'    => $obj->display()->type,
  'entries' => Menu::build($app, $obj)
];
