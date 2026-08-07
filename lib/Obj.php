<?php

namespace Sys;

/**
 * One object of the hierarchy.
 *
 * The title is never stored: it is the file or folder name, so there is
 * exactly one source of truth for it (see plan §2.3).
 */
class Obj
{
  public string $path   = '';     // relative, without the .md extension
  public string $title  = '';     // the file or folder name, link marker stripped
  public string $file   = '';     // relative file holding it, '' for a bare group folder
  public string $type   = '';
  public string $id     = '';
  public string $target = '';     // links only: id of the target
  public array  $data   = [];     // front matter without the reserved keys
  public string $body   = '';
  public int    $mtime  = 0;
  public string $created  = '';
  public string $modified = '';

  public bool $isFolder   = false;
  public bool $isLink     = false;
  public bool $isResource = false;

  public ?Obj $linkTarget = null;   // resolved target of a link stub

  public function __construct( string $path, string $type )
  {
    $this->path = $path;
    $this->type = $type;
  }

  /**
   * Raw file or folder name, including a link marker if present.
   */
  public function name() : string
  {
    $pos = strrpos($this->path, '/');
    return $pos === false ? $this->path : substr($this->path, $pos + 1);
  }

  public function parentPath() : string
  {
    $pos = strrpos($this->path, '/');
    return $pos === false ? '' : substr($this->path, 0, $pos);
  }

  /**
   * The object a card should be rendered from: for a link that is its target.
   */
  public function display() : Obj
  {
    return $this->linkTarget ?? $this;
  }

  public function field( string $key, $default = null )
  {
    return $this->data[$key] ?? $default;
  }
}
