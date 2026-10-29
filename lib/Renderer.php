<?php

namespace Sys;

/**
 * Base of every renderer. A viewer overrides only the kinds it needs, the
 * defaults below keep unknown and simple types fully usable.
 */
abstract class Renderer
{
  protected App $app;
  protected Viewer $viewer;
  protected Type $type;
  protected Obj $obj;

  // Parent path, set only by lists that show entries from anywhere
  protected ?string $context = null;

  public function __construct( App $app, Viewer $viewer, Obj $obj )
  {
    $this->app    = $app;
    $this->viewer = $viewer;
    $this->type   = $viewer->type;
    $this->obj    = $obj;
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

  // Helpers for the viewer renderers

  protected function esc( $value ) : string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }

  /**
   * A button running an action entry: id, label, optional icon and title,
   * and either "ajax" (handler in the viewer's ajax folder, with optional
   * "params") or "js" (handler in window.SYS_TYPES). The same entries as in
   * the "..." menu, so the controller runs them the same way.
   */
  protected function actionButton( array $entry, string $class ) : string
  {
    $entry = $this->action($entry);
    $icon  = isset($entry['icon']) ? $this->icon($entry['icon']) : '';

    return '<button type="button" class="' . $this->esc($class) . '" data-action="viewerAction"'
      . ' title="' . $this->esc($entry['title'] ?? $entry['label']) . '"'
      . ' data-entry="' . $this->esc(json_encode($entry)) . '">'
      . $icon . '<span>' . $this->esc($entry['label']) . '</span></button>';
  }

  /**
   * Adds what the ajax router needs to find the handler of this viewer.
   */
  protected function action( array $entry ) : array
  {
    return $entry + ['type' => $this->type->name, 'viewer' => $this->viewer->name];
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

    $label = $this->app->path->label($this->context);

    return '<button type="button" class="card-path" data-action="goPath" title="Go to this container" data-path="'
      . $this->esc($this->context) . '">' . $this->esc($label) . '</button>';
  }

  /**
   * The fixed values in the object's name, e.g. its tags or its prio, as
   * small chips. Every card shows them, so a type's own badges need not care.
   */
  protected function tags() : string
  {
    $out = '';

    foreach( $this->app->path->fields($this->obj->type)->fixedValues($this->obj->nameData) as $tag )
    {
      $class = 'tag' . ($tag['sort'] ? ' tag-rank' : '') . ($tag['selected'] !== '' ? ' tag-selected' : '');

      $out .= '<span class="' . $class . '" data-field="' . $this->esc($tag['field']) . '" data-value="' . $this->esc($tag['value']) . '"'
        . ' title="' . $this->esc($tag['value']) . '">' . $this->esc($tag['label']) . '</span>';
    }

    return $out;
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
