<?php

/**
 * The editor for one object, plus the header and footer content and the
 * viewer switch of its viewer.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Menu;
use Sys\View;

$obj = $ajax->obj();

// Opening a link means opening what it points at
if( $obj->isLink && $obj->target !== '' )
{
  $target = $app->index->obj($obj->target);

  if( $target !== null )
    $obj = $target;
}

$app->recent->push($obj->id);

$viewer = $app->viewers->forObj($obj);

return [
  'path'    => $obj->path,
  'title'   => $obj->title,
  'type'    => $obj->type,
  'id'      => $obj->id,
  'mtime'   => $obj->mtime,
  'editor'  => $app->renderer('detail', $obj)->html(),
  'header'  => $app->renderer('header', $obj)->html(),
  'footer'  => $viewer->get('footer', false) ? $app->renderer('footer', $obj)->html() : '',
  'viewers' => View::render("{$app->root}/view/viewer_switch.php", ['app' => $app, 'viewer' => $viewer]),
  'menu'    => Menu::build($app, $obj)
];
