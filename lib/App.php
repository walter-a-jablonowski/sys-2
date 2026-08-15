<?php

namespace Sys;

/**
 * Boots the application and wires the services together.
 */
class App
{
  public string $root;
  public Config $config;
  public Db $db;
  public ObjPath $path;
  public ObjStore $store;
  public Index $index;
  public TypeRegistry $types;

  private array $assets = [];

  private static ?App $instance = null;

  private function __construct( string $root )
  {
    $this->root = rtrim(str_replace('\\', '/', $root), '/');

    $appFile = "{$this->root}/config.yml";
    $dbPath  = "{$this->root}/" . (Config::read($appFile)['dbPath'] ?? 'data/demo');

    $this->config = new Config($appFile, "{$dbPath}/.sys/config.yml");
    $this->db     = new Db($dbPath, $this->config);
    $this->path   = new ObjPath($this->db, $this->config);
    $this->store  = new ObjStore($this->db, $this->path, $this->config);
    $this->index  = new Index($this->db, $this->store);
    $this->types  = new TypeRegistry("{$this->root}/types", $this->config);
  }

  /**
   * Url of an own asset with a cache busting stamp, so an edited file is
   * never served from the browser cache. Resolved once per file per request.
   */
  public function asset( string $rel ) : string
  {
    if( ! isset($this->assets[$rel]))
    {
      $file = "{$this->root}/{$rel}";

      $this->assets[$rel] = $rel . '?v=' . (is_file($file) ? filemtime($file) : 0);
    }

    return $this->assets[$rel];
  }

  /**
   * Renderer of a kind for an object, resolved through its type.
   */
  public function renderer( string $kind, Obj $obj ) : Renderer
  {
    return $this->types->forObj($obj)->renderer($kind, $this, $obj);
  }

  public static function boot( string $root ) : App
  {
    if( self::$instance === null )
      self::$instance = new self($root);

    return self::$instance;
  }

  public static function get() : App
  {
    if( self::$instance === null )
      throw new Failure('App not booted', 500);

    return self::$instance;
  }
}
