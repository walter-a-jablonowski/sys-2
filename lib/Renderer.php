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

  // Parent path, set only by lists that show entries from anywhere
  protected ?string $context = null;

  public function __construct( App $app, Type $type, Obj $obj )
  {
    $this->app  = $app;
    $this->type = $type;
    $this->obj  = $obj;
  }

  abstract public function render() : void;

  /**
   * Tells the renderer to name the container the object lives in. Needed
   * by the recent and pinned lists, where the title alone says too little.
   */
  public function withContext( string $parentPath ) : self
  {
    $this->context = $parentPath;

    return $this;
  }

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
    $sprite = $this->esc($this->app->asset('styles/icons.svg'));

    return '<svg class="icon" aria-hidden="true"><use href="' . $sprite . '#' . $this->esc($name) . '"></use></svg>';
  }

  /**
   * The container an object lives in, as a line under its title. Empty
   * unless a list asked for it, so any render() can emit it unguarded.
   * An object at the top level is named by the database itself.
   */
  protected function contextBlock() : string
  {
    if( $this->context === null )
      return '';

    $label = $this->app->db->pathLabel($this->context);

    return '<button type="button" class="card-path" data-action="goPath" title="Go to this container" data-path="'
      . $this->esc($this->context) . '">' . $this->esc($label) . '</button>';
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
