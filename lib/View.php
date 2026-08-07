<?php

namespace Sys;

/**
 * Renders a view file to a string, so the first paint and every later ajax
 * update go through exactly the same templates.
 */
class View
{
  public static function render( string $file, array $vars ) : string
  {
    extract($vars);

    ob_start();
    require $file;

    return (string) ob_get_clean();
  }
}
