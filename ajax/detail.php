<?php

/**
 * The editor for one object, plus the header and footer content of its type.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Menu;

$obj = $ajax->obj();

// Opening a link means opening what it points at
if( $obj->isLink && $obj->target !== '' )
{
  $target = $app->index->obj($obj->target);

  if( $target !== null )
    $obj = $target;
}

$type = $app->types->forObj($obj);

return [
  'path'   => $obj->path,
  'title'  => $obj->title,
  'type'   => $obj->type,
  'id'     => $obj->id,
  'mtime'  => $obj->mtime,
  'editor' => $app->renderer('detail', $obj)->html(),
  'header' => $app->renderer('header', $obj)->html(),
  'footer' => $type->get('footer', false) ? $app->renderer('footer', $obj)->html() : '',
  'menu'   => Menu::build($app, $obj)
];
