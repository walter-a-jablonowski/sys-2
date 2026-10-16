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
  public UserSettings $user;
  public ViewerRegistry $viewers;
  public ObjList $recent;
  public ObjList $pinned;

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
    $this->types   = new TypeRegistry("{$this->root}/types", $this->config);
    $this->user    = new UserSettings("{$this->root}/data/users/default/settings.yml");  // single user for now
    $this->viewers = new ViewerRegistry($this->root, $this->types, $this->user);
    $this->recent  = new ObjList($this->db, $this->index, 'recent', 50);
    $this->pinned  = new ObjList($this->db, $this->index, 'pinned');
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
   * Renderer of a kind for an object, resolved through the viewer of its type.
   */
  public function renderer( string $kind, Obj $obj ) : Renderer
  {
    return $this->viewers->forObj($obj)->renderer($kind, $this, $obj);
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
