<?php

namespace Sys;

/**
 * Base of every renderer. A type overrides only the kinds it needs, the
 * defaults below keep unknown and simple types fully usable.
 */
abstract class Renderer
{
  protected App $app;
  protected Type $type;
  protected Obj $obj;

  public function __construct( App $app, Type $type, Obj $obj )
  {
    $this->app  = $app;
    $this->type = $type;
    $this->obj  = $obj;
  }

  abstract public function render() : void;

  public function html() : string
  {
    ob_start();
    $this->render();

    return (string) ob_get_clean();
  }

  // Helpers for the type renderers

  protected function esc( $value ) : string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }

  protected function icon( string $name ) : string
  {
    return '<svg class="icon" aria-hidden="true"><use href="styles/icons.svg#' . $this->esc($name) . '"></use></svg>';
  }

  protected function excerpt( string $text, int $max = 90 ) : string
  {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));

    if( mb_strlen($text) <= $max )
      return $text;

    return mb_substr($text, 0, $max - 1) . '…';
  }

  protected function dateShort( string $iso ) : string
  {
    if( $iso === '' )
      return '';

    $time = strtotime($iso);

    if( $time === false )
      return '';

    return date('Y', $time) === date('Y') ? date('j M', $time) : date('j M Y', $time);
  }

  /**
   * Number of children without reading them, for the meta line of a container.
   */
  protected function childCount() : int
  {
    return $this->obj->isFolder ? $this->app->store->countChildren($this->obj->path) : 0;
  }
}
