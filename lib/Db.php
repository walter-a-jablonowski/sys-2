<?php

namespace Sys;

/**
 * The database root: path safety and health check.
 *
 * Every relative path used by the app is cleaned here, so no request can
 * reach outside the database or into the hidden .sys folder.
 */
class Db
{
  private string $root;
  private Config $config;

  public function __construct( string $root, Config $config )
  {
    $this->root   = rtrim(str_replace('\\', '/', $root), '/');
    $this->config = $config;
  }

  public function root() : string
  {
    return $this->root;
  }

  public function name() : string
  {
    return basename($this->root);
  }

  public function abs( string $rel = '' ) : string
  {
    $rel = $this->clean($rel);
    return $rel === '' ? $this->root : "{$this->root}/{$rel}";
  }

  public function sysDir() : string
  {
    return "{$this->root}/.sys";
  }

  /**
   * Rejects traversal and hidden segments, so ".sys" stays unreachable.
   */
  public function clean( string $rel ) : string
  {
    $parts = [];

    foreach( explode('/', str_replace('\\', '/', $rel)) as $part )
    {
      if( $part === '' || $part === '.' )
        continue;

      if( $part === '..' || strpos($part, '.') === 0 )
        throw new Failure("Invalid path segment: $part", 400);

      $parts[] = $part;
    }

    return implode('/', $parts);
  }

  /**
   * Health report: shadowed files, duplicate ids, broken links.
   */
  public function check( ObjStore $store, Index $index ) : array
  {
    $issues = [];

    foreach( $index->duplicates() as $id => $paths )
      $issues[] = ['kind' => 'duplicateId', 'id' => $id, 'paths' => $paths];

    foreach( $store->walk('') as $obj )
    {
      if( $obj->isFolder && is_file($this->abs("{$obj->path}.md")))
        $issues[] = ['kind' => 'shadowed', 'path' => "{$obj->path}.md"];

      if( $obj->isLink && $index->path($obj->target) === null )
        $issues[] = ['kind' => 'brokenLink', 'path' => $obj->path, 'target' => $obj->target];
    }

    return $issues;
  }
}
