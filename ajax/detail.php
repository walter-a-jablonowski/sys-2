<?php

/**
 * The editor for one object, plus the frame its viewer draws around it.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Frame;
use Sys\Menu;

$obj = $ajax->obj();

// Opening a link means opening what it points at
if( $obj->isLink )
  $obj = $app->index->target($obj) ?? $obj;

$app->recent->push($obj->id);

return [
  'path'   => $obj->path,
  'title'  => $obj->title,
  'type'   => $obj->type,
  'id'     => $obj->id,
  'mtime'  => $obj->mtime,
  'editor' => $app->renderer('detail', $obj)->html(),
  'menu'   => Menu::build($app, $obj)
] + Frame::build($app, $obj);
