<?php

namespace Sys;

/**
 * id -> path cache in <db>/.sys/index.json, plus the alternative ids some
 * databases carry in their names (NameFields role "id").
 *
 * This class is the only place that knows the storage format, so it can be
 * swapped for a real store later without touching anything else (plan §2.5).
 *
 * It also hands out the ids: it is the one that knows which ones are taken.
 */
class Index
{
  private Db $db;
  private ObjStore $store;
  private TypeRegistry $types;
  private string $file;
  private array $data;
  private bool $loaded = false;

  public function __construct( Db $db, ObjStore $store, TypeRegistry $types )
  {
    $this->db    = $db;
    $this->store = $store;
    $this->types = $types;
    $this->file  = "{$db->sysDir()}/index.json";
    $this->data  = ['builtAt' => 0, 'objects' => [], 'altIds' => [], 'links' => [], 'duplicates' => [], 'altDuplicates' => []];
  }

  public function path( string $id ) : ?string
  {
    $this->load();
    return $this->data['objects'][$id]['path'] ?? null;
  }

  public function altPath( string $altId ) : ?string
  {
    $this->load();
    return $this->data['altIds'][$altId]['path'] ?? null;
  }

  public function obj( string $id ) : ?Obj
  {
    $path = $this->path($id);
    return $path === null ? null : $this->store->get($path);
  }

  /**
   * What a link points at: by the id in its front matter, else by the
   * alternative id in its name. Null for a broken link.
   */
  public function target( Obj $link ) : ?Obj
  {
    if( $link->target !== '' )
      return $this->obj($link->target);

    $path = $link->altTarget === '' ? null : $this->altPath($link->altTarget);

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

  public function altDuplicates() : array
  {
    $this->load();
    return $this->data['altDuplicates'];
  }

  public function all() : array
  {
    $this->load();
    return $this->data['objects'];
  }

  /**
   * The id itself when it is free, else the first free one of "ID-2",
   * "ID-3", ... Generated ids end with the minute, so two entries of one
   * title made within a minute would get the same one.
   */
  public function freeId( string $id ) : string
  {
    $this->load();
    return self::unique($this->data['objects'], $id);
  }

  /**
   * Gives an object without id one and writes it to disk. For a folder or a
   * resource that is the moment its content file or sidecar comes to be,
   * so it is done only when something needs the id (pin, link to it).
   * A link gets none, it points at one.
   */
  public function ensureId( Obj $obj ) : Obj
  {
    if( $obj->id !== '' || $obj->isLink )
      return $obj;

    $obj = $this->store->addId($obj, $this->freeId($this->types->forObj($obj)->newId($obj->title)));

    $this->invalidate();

    return $obj;
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

  /**
   * Reads every object. A .md entry without id gets one here, written into
   * its front matter: an object without id cannot be searched, pinned or
   * linked to. Folders and resources would need a new file for it, they get
   * theirs when needed (ensureId).
   */
  public function build() : void
  {
    $objects       = [];
    $altIds        = [];
    $links         = [];
    $altLinks      = [];
    $duplicates    = [];
    $altDuplicates = [];
    $idOfPath      = [];
    $entries       = [];

    foreach( $this->store->walk('') as $obj )
    {
      if( $obj->altId !== '' )
        self::claim($altIds, $altDuplicates, $obj->altId, ['path' => $obj->path]);

      if( $obj->isLink )
      {
        if( $obj->target !== '' )
          $links[$obj->target][] = $obj->path;
        else
          $altLinks[$obj->altTarget][] = $obj->path;

        continue;
      }

      $entries[] = $obj;
    }

    // Objects with an id first, so a new id is handed out only once every taken one is known
    usort($entries, fn( Obj $a, Obj $b ) => ($a->id === '') <=> ($b->id === ''));

    foreach( $entries as $obj )
    {
      if( $obj->id === '' && $obj->file !== '' && ! $obj->isResource )
        $obj = $this->store->addId($obj, self::unique($objects, $this->types->forObj($obj)->newId($obj->title)));

      if( $obj->id !== '' )
      {
        self::claim($objects, $duplicates, $obj->id, ['path' => $obj->path, 'type' => $obj->type, 'mtime' => $obj->mtime]);
        $idOfPath[$obj->path] = $obj->id;
      }
    }

    // A link by alternative id is a backlink of the id behind it
    foreach( $altLinks as $altId => $paths )
    {
      $id = $idOfPath[$altIds[$altId]['path'] ?? ''] ?? null;

      if( $id !== null )
        $links[$id] = array_merge($links[$id] ?? [], $paths);
    }

    $this->data = [
      'builtAt'       => time(),
      'objects'       => $objects,
      'altIds'        => $altIds,
      'links'         => $links,
      'duplicates'    => $duplicates,
      'altDuplicates' => $altDuplicates
    ];

    $this->loaded = true;
    $this->store();
  }

  // Internals

  private static function unique( array $taken, string $id ) : string
  {
    $try = $id;
    $n   = 1;

    while( isset($taken[$try]))
    {
      $n++;
      $try = "{$id}-{$n}";
    }

    return $try;
  }

  /**
   * Records an entry under its key. A key already taken keeps its first
   * entry, the paths of all of them are noted as duplicates.
   */
  private static function claim( array &$map, array &$duplicates, string $key, array $entry ) : void
  {
    if( ! isset($map[$key]))
    {
      $map[$key] = $entry;
      return;
    }

    $duplicates[$key] = array_merge($duplicates[$key] ?? [$map[$key]['path']], [$entry['path']]);
  }

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
   * Any folder touched after the last build means the tree may have changed,
   * a changed database config that names are read differently.
   */
  private function isStale() : bool
  {
    $config = "{$this->db->sysDir()}/config.yml";
    $newest = max($this->newestDir($this->db->root()), is_file($config) ? (int) filemtime($config) : 0);

    return $newest > (int) $this->data['builtAt'];
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
