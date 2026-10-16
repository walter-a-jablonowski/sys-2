<?php

namespace Sys;

/**
 * Everything the object's viewer draws around the editor: header content,
 * toolbar and viewer switch in the head band, footer content and status bar
 * in the foot band. Opening and saving an object answer with the same parts.
 */
class Frame
{
  public static function build( App $app, Obj $obj ) : array
  {
    $viewer = $app->viewers->forObj($obj);

    return [
      'header'    => $viewer->renderer('header', $app, $obj)->html(),
      'toolbar'   => $viewer->renderer('toolbar', $app, $obj)->html(),
      'viewers'   => View::render("{$app->root}/view/viewer_switch.php", ['app' => $app, 'viewer' => $viewer]),
      'footer'    => $viewer->get('footer', false) ? $viewer->renderer('footer', $app, $obj)->html() : '',
      'statusbar' => $viewer->renderer('statusbar', $app, $obj)->html()
    ];
  }
}
