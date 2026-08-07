<?php

namespace Sys;

/**
 * One entry type: its configuration and the renderers it brings along.
 */
class Type
{
  // Renderer kind -> file in the type folder
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
  public string $dir;
  public array $config;

  public function __construct( string $name, string $dir, array $config )
  {
    $this->name   = $name;
    $this->dir    = $dir;
    $this->config = $config;
  }

  public function label() : string
  {
    return $this->config['label'] ?? $this->name;
  }

  public function icon() : string
  {
    return $this->config['icon'] ?? 'entry';
  }

  public function get( string $key, $default = null )
  {
    return $this->config[$key] ?? $default;
  }

  public function fields() : array
  {
    return $this->config['fields'] ?? [];
  }

  public function tabs() : array
  {
    return $this->config['tabs'] ?? [];
  }

  /**
   * Types that can be created in a tab, plus the special "__link:Type" entry.
   */
  public function createOptions( string $tab ) : array
  {
    return $this->config['create'][$tab] ?? [];
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
   * Generates an id from the type's pattern. Ids are free strings, this is
   * only the generator for new objects (plan §2.3).
   */
  public function newId( string $title ) : string
  {
    $pattern = $this->config['idPattern'] ?? '{slug}-{YYYY}-{MM}-{DD}-{HHmm}';

    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/u', '-', $slug);
    $slug = trim((string) $slug, '-');

    if( $slug === '' )
      $slug = strtolower($this->name);

    return str_replace(
      ['{slug}', '{type}', '{YYYY}', '{MM}', '{DD}', '{HHmm}'],
      [$slug, strtolower($this->name), date('Y'), date('m'), date('d'), date('Hi')],
      $pattern
    );
  }

  public function newName() : string
  {
    return 'New ' . $this->label();
  }

  /**
   * Renderer of a kind: the type's own class if it ships one, else the default.
   */
  public function renderer( string $kind, App $app, Obj $obj ) : Renderer
  {
    $class = self::DEFAULTS[$kind] ?? ListRenderer::class;
    $file  = "{$this->dir}/" . self::RENDERERS[$kind];

    if( is_file($file))
    {
      require_once $file;

      $own = $this->name . ucfirst($kind) . 'Renderer';

      if( class_exists($own))
        $class = $own;
    }

    return new $class($app, $this, $obj);
  }
}
