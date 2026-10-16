<?php

namespace Sys;

/**
 * One way of showing a type, read from type_viewer/MY_TYPE/MY_VIEWER: the
 * renderers, styles, script, ajax actions, menu and editor tabs. A type may
 * bring several viewers, the ViewerRegistry decides which one is shown.
 */
class Viewer
{
  // Renderer kind -> file in the viewer folder
  private const RENDERERS = [
    'list'   => 'list_renderer.php',
    'mobile' => 'mobile_renderer.php',
    'detail' => 'detail_renderer.php',
    'header' => 'header_renderer.php',
    'footer' => 'footer_renderer.php'
  ];

  private const DEFAULTS = [
    'list'   => ListRenderer::class,
    'mobile' => MobileRenderer::class,
    'detail' => DetailRenderer::class,
    'header' => HeaderRenderer::class,
    'footer' => FooterRenderer::class
  ];

  public string $name;
  public Type $type;
  public string $path;     // relative to the app root, for asset urls
  public string $dir;
  public array $config;

  public function __construct( string $name, Type $type, string $path, string $dir, array $config )
  {
    $this->name   = $name;
    $this->type   = $type;
    $this->path   = $path;
    $this->dir    = $dir;
    $this->config = $config;
  }

  public function label() : string
  {
    return $this->config['label'] ?? ucfirst($this->name);
  }

  public function get( string $key, $default = null )
  {
    return $this->config[$key] ?? $default;
  }

  public function detailTabs() : array
  {
    return $this->config['detailTabs'] ?? [];
  }

  public function menu() : array
  {
    return $this->config['menu'] ?? [];
  }

  public function hasStyles() : bool
  {
    return is_file("{$this->dir}/styles.css");
  }

  public function hasScript() : bool
  {
    return is_file("{$this->dir}/controller.js");
  }

  /**
   * Renderer of a kind: the viewer's own class if it ships one, else the
   * default. Own classes are named MY_TYPE MY_VIEWER KIND "Renderer", e.g.
   * ActivityDefaultListRenderer, so two viewers of one type never collide.
   */
  public function renderer( string $kind, App $app, Obj $obj ) : Renderer
  {
    $class = self::DEFAULTS[$kind] ?? ListRenderer::class;
    $file  = "{$this->dir}/" . self::RENDERERS[$kind];

    if( is_file($file))
    {
      require_once $file;

      $own = $this->type->name . ucfirst($this->name) . ucfirst($kind) . 'Renderer';

      if( class_exists($own))
        $class = $own;
    }

    return new $class($app, $this, $obj);
  }
}
