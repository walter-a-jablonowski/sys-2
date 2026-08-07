<?php

namespace Sys;

/**
 * Reads and writes objects. Everything that touches the file system of the
 * database goes through here.
 */
class ObjStore
{
  private Db $db;
  private ObjPath $path;
  private Config $config;

  // Front matter keys the app owns, everything else belongs to the type
  private const RESERVED = ['id', 'type', 'created', 'modified', 'target'];

  public function __construct( Db $db, ObjPath $path, Config $config )
  {
    $this->db     = $db;
    $this->path   = $path;
    $this->config = $config;
  }

  public function path() : ObjPath
  {
    return $this->path;
  }

  /**
   * Loads one object, null when nothing lives at that path.
   */
  public function get( string $rel ) : ?Obj
  {
    $rel = $this->db->clean($rel);

    if( $rel === '' )
      return $this->rootObj();

    $abs  = $this->db->abs($rel);
    $file = $this->path->fileOf($rel);

    if( $file === null )
      return is_dir($abs) ? $this->groupObj($rel) : null;

    return $this->read($rel, $file, is_dir($abs));
  }

  /**
   * All children of a container, unfiltered and unsorted (see ChildQuery).
   *
   * @return Obj[]
   */
  public function children( string $rel ) : array
  {
    $rel = $this->db->clean($rel);
    $abs = $this->db->abs($rel);

    if( ! is_dir($abs))
      return [];

    $out = [];

    foreach( scandir($abs) as $entry )
    {
      $childPath = $this->path->pathOfEntry($rel, $entry);

      if( $childPath === null )
        continue;

      $child = $this->get($childPath);

      if( $child !== null )
        $out[] = $child;
    }

    return $out;
  }

  /**
   * Counts children without reading them, for the meta line of a card.
   */
  public function countChildren( string $rel ) : int
  {
    $abs = $this->db->abs($this->db->clean($rel));

    if( ! is_dir($abs))
      return 0;

    $n = 0;

    foreach( scandir($abs) as $entry )
    {
      if( $this->path->pathOfEntry($rel, $entry) !== null )
        $n++;
    }

    return $n;
  }

  /**
   * Every object below a path, depth first.
   *
   * @return Obj[]
   */
  public function walk( string $rel ) : array
  {
    $out = [];

    foreach( $this->children($rel) as $child )
    {
      $out[] = $child;

      if( $child->isFolder )
        $out = array_merge($out, $this->walk($child->path));
    }

    return $out;
  }

  public function create( string $parentRel, string $type, string $title, string $id ) : Obj
  {
    $parentRel = $this->containerFor($parentRel);
    $name      = $this->path->freeName($parentRel, $this->path->sanitize($title));
    $rel       = $parentRel === '' ? $name : "{$parentRel}/{$name}";
    $now       = date('c');

    $this->write("{$rel}.md", ['id' => $id, 'type' => $type, 'created' => $now, 'modified' => $now], '');

    return $this->get($rel);
  }

  public function createLink( string $parentRel, string $title, string $targetId ) : Obj
  {
    $parentRel = $this->containerFor($parentRel);
    $name      = $this->path->freeName($parentRel, $this->path->linkName($title));
    $rel       = $parentRel === '' ? $name : "{$parentRel}/{$name}";
    $now       = date('c');

    $this->write("{$rel}.md", ['type' => 'Link', 'target' => $targetId, 'created' => $now], '');

    return $this->get($rel);
  }

  /**
   * Writes fields and body back. $mtime is the value the client loaded with,
   * a mismatch means someone else changed the file meanwhile.
   */
  public function save( Obj $obj, array $data, string $body, ?int $mtime = null ) : Obj
  {
    $file = $obj->isResource ? $this->path->sidecarOf($obj->path) : $obj->file;

    if( $mtime !== null && $obj->mtime !== 0 && $obj->mtime !== $mtime )
      throw Failure::conflict('The file changed since it was loaded', ['mtime' => $obj->mtime]);

    $head = ['id' => $obj->id, 'type' => $obj->type];

    if( $obj->target !== '' )
      $head['target'] = $obj->target;

    $head['created']  = $obj->created !== '' ? $obj->created : date('c');
    $head['modified'] = date('c');

    $this->write($file, array_merge($head, $this->clean($data)), $body);

    return $this->get($obj->path);
  }

  /**
   * Renaming the title renames the file or folder (plan §2.8).
   */
  public function rename( Obj $obj, string $title ) : Obj
  {
    $name = $obj->isLink ? $this->path->linkName($title) : $this->path->sanitize($title);

    if( $obj->isResource )
      $name = $this->path->sanitize($title);

    if( $name === $obj->name())
      return $obj;

    $parent = $obj->parentPath();
    $suffix = $obj->isFolder || $obj->isResource ? '' : '.md';
    $name   = $this->path->freeName($parent, $name, $suffix);

    return $this->relocate($obj, $parent, $name);
  }

  public function move( Obj $obj, string $newParentRel ) : Obj
  {
    $newParentRel = $this->containerFor($newParentRel);

    if( $newParentRel === $obj->path || strpos("{$newParentRel}/", "{$obj->path}/") === 0 )
      throw Failure::invalid('An object cannot be moved into itself');

    $suffix = $obj->isFolder || $obj->isResource ? '' : '.md';
    $name   = $this->path->freeName($newParentRel, $obj->name(), $suffix);

    return $this->relocate($obj, $newParentRel, $name);
  }

  public function delete( Obj $obj ) : void
  {
    $abs = $this->db->abs($obj->path);

    if( $obj->isFolder )
    {
      $this->removeTree($abs);
      return;
    }

    if( $obj->isResource )
    {
      $sidecar = $this->db->abs($this->path->sidecarOf($obj->path));

      if( is_file($sidecar))
        unlink($sidecar);
    }

    if( $obj->file !== '' && is_file($this->db->abs($obj->file)))
      unlink($this->db->abs($obj->file));
  }

  /**
   * Turns "X.md" into "X/-this.md" so the object can hold children.
   */
  public function promote( Obj $obj ) : Obj
  {
    if( $obj->isFolder )
      return $obj;

    $dir = $this->db->abs($obj->path);

    if( ! mkdir($dir) && ! is_dir($dir))
      throw new Failure("Could not create folder: {$obj->path}", 500);

    rename($this->db->abs($obj->file), "{$dir}/{$this->path->thisFileName()}");

    return $this->get($obj->path);
  }

  // Internals

  /**
   * Makes sure a parent can hold children, promoting a leaf if needed.
   */
  private function containerFor( string $rel ) : string
  {
    $rel = $this->db->clean($rel);

    if( $rel === '' || is_dir($this->db->abs($rel)))
      return $rel;

    $obj = $this->get($rel);

    if( $obj === null )
      throw Failure::notFound($rel);

    $this->promote($obj);

    return $rel;
  }

  private function relocate( Obj $obj, string $parentRel, string $name ) : Obj
  {
    $target = $parentRel === '' ? $name : "{$parentRel}/{$name}";
    $from   = $obj->isFolder ? $obj->path : $obj->file;
    $to     = $obj->isFolder || $obj->isResource ? $target : "{$target}.md";

    if( $obj->isResource )
    {
      $sidecar = $this->db->abs($this->path->sidecarOf($obj->path));

      if( is_file($sidecar))
        rename($sidecar, $this->db->abs($this->path->sidecarOf($target)));
    }

    rename($this->db->abs($from), $this->db->abs($to));

    return $this->get($target);
  }

  private function read( string $rel, string $file, bool $isFolder ) : Obj
  {
    $absFile = $this->db->abs($file);
    $parsed  = FrontMatter::parse((string) file_get_contents($absFile));

    if( $parsed['data'] === null )
      return $this->resourceObj($rel, $file);

    $data = $parsed['data'];
    $obj  = new Obj($rel, (string) ($data['type'] ?? $this->config->db('defaultType', 'Info')));

    $obj->file     = $file;
    $obj->title    = $this->path->stripMarker($obj->name());
    $obj->id       = (string) ($data['id'] ?? '');
    $obj->target   = (string) ($data['target'] ?? '');
    $obj->created  = (string) ($data['created'] ?? '');
    $obj->modified = (string) ($data['modified'] ?? '');
    $obj->body     = $parsed['body'];
    $obj->data     = $this->clean($data);
    $obj->mtime    = (int) filemtime($absFile);
    $obj->isFolder = $isFolder;
    $obj->isLink   = $obj->type === 'Link';

    return $obj;
  }

  /**
   * A file without front matter is a resource, its metadata may live in a
   * sidecar with the same name plus ".md".
   */
  private function resourceObj( string $rel, string $file ) : Obj
  {
    $obj = new Obj($rel, 'File');

    $obj->file       = $file;
    $obj->title      = $obj->name();
    $obj->isResource = true;
    $obj->mtime      = (int) filemtime($this->db->abs($file));

    $sidecar = $this->path->sidecarOf($rel);
    $abs     = $this->db->abs($sidecar);

    if( is_file($abs))
    {
      $parsed = FrontMatter::parse((string) file_get_contents($abs));
      $data   = $parsed['data'] ?? [];

      $obj->id       = (string) ($data['id'] ?? '');
      $obj->created  = (string) ($data['created'] ?? '');
      $obj->modified = (string) ($data['modified'] ?? '');
      $obj->body     = $parsed['body'];
      $obj->data     = $this->clean($data);
      $obj->mtime    = (int) filemtime($abs);
    }

    return $obj;
  }

  private function groupObj( string $rel ) : Obj
  {
    $obj = new Obj($rel, 'Group');

    $obj->title    = $this->path->stripMarker($obj->name());
    $obj->isFolder = true;
    $obj->mtime    = (int) filemtime($this->db->abs($rel));

    return $obj;
  }

  private function rootObj() : Obj
  {
    $obj = new Obj('', 'Group');

    $obj->title    = $this->db->name();
    $obj->isFolder = true;
    $obj->mtime    = (int) filemtime($this->db->root());

    return $obj;
  }

  private function clean( array $data ) : array
  {
    return array_diff_key($data, array_flip(self::RESERVED));
  }

  private function write( string $file, array $data, string $body ) : void
  {
    $abs = $this->db->abs($file);
    $dir = dirname($abs);

    if( ! is_dir($dir))
      mkdir($dir, 0777, true);

    file_put_contents($abs, FrontMatter::build($data, $body));
  }

  private function removeTree( string $abs ) : void
  {
    foreach( scandir($abs) as $entry )
    {
      if( $entry === '.' || $entry === '..' )
        continue;

      $child = "{$abs}/{$entry}";

      is_dir($child) ? $this->removeTree($child) : unlink($child);
    }

    rmdir($abs);
  }
}
