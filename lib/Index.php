<?php

namespace Sys;

/**
 * id -> path cache in <db>/.sys/index.json.
 *
 * This class is the only place that knows the storage format, so it can be
 * swapped for a real store later without touching anything else (plan §2.5).
 */
class Index
{
  private Db $db;
  private ObjStore $store;
  private string $file;
  private array $data;
  private bool $loaded = false;

  public function __construct( Db $db, ObjStore $store )
  {
    $this->db    = $db;
    $this->store = $store;
    $this->file  = "{$db->sysDir()}/index.json";
    $this->data  = ['builtAt' => 0, 'objects' => [], 'links' => [], 'duplicates' => []];
  }

  public function path( string $id ) : ?string
  {
    $this->load();
    return $this->data['objects'][$id]['path'] ?? null;
  }

  public function obj( string $id ) : ?Obj
  {
    $path = $this->path($id);
    return $path === null ? null : $this->store->get($path);
  }

  /**
   * Paths of the link stubs pointing at an object.
   */
  public function backlinks( string $id ) : array
  {
    $this->load();
    return $this->data['links'][$id] ?? [];
  }

  public function duplicates() : array
  {
    $this->load();
    return $this->data['duplicates'];
  }

  public function all() : array
  {
    $this->load();
    return $this->data['objects'];
  }

  /**
   * Called after every write so the cache never lags behind the app itself.
   */
  public function invalidate() : void
  {
    $this->data['builtAt'] = 0;
    $this->loaded          = false;

    if( is_file($this->file))
      unlink($this->file);
  }

  public function build() : void
  {
    $objects    = [];
    $links      = [];
    $duplicates = [];

    foreach( $this->store->walk('') as $obj )
    {
      if( $obj->isLink )
      {
        $links[$obj->target][] = $obj->path;
        continue;
      }

      if( $obj->id === '' )
        continue;

      if( isset($objects[$obj->id]))
      {
        $duplicates[$obj->id] = array_merge(
          $duplicates[$obj->id] ?? [$objects[$obj->id]['path']],
          [$obj->path]
        );
        continue;
      }

      $objects[$obj->id] = ['path' => $obj->path, 'type' => $obj->type, 'mtime' => $obj->mtime];
    }

    $this->data = [
      'builtAt'    => time(),
      'objects'    => $objects,
      'links'      => $links,
      'duplicates' => $duplicates
    ];

    $this->loaded = true;
    $this->store();
  }

  // Internals

  private function load() : void
  {
    if( $this->loaded )
      return;

    if( is_file($this->file))
    {
      $data = json_decode((string) file_get_contents($this->file), true);

      if( is_array($data))
      {
        $this->data   = array_replace($this->data, $data);
        $this->loaded = true;
      }
    }

    if( ! $this->loaded || $this->isStale())
      $this->build();

    $this->loaded = true;
  }

  /**
   * Any folder touched after the last build means the tree may have changed.
   */
  private function isStale() : bool
  {
    return $this->newestDir($this->db->root()) > (int) $this->data['builtAt'];
  }

  private function newestDir( string $abs ) : int
  {
    $newest = (int) filemtime($abs);

    foreach( scandir($abs) as $entry )
    {
      if( $entry === '.' || $entry === '..' || strpos($entry, '.') === 0 )
        continue;

      $child = "{$abs}/{$entry}";

      if( is_dir($child))
        $newest = max($newest, $this->newestDir($child));
    }

    return $newest;
  }

  private function store() : void
  {
    if( ! is_dir($this->db->sysDir()))
      mkdir($this->db->sysDir(), 0777, true);

    file_put_contents($this->file, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  }
}
