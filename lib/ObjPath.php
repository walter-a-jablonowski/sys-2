<?php

namespace Sys;

/**
 * Naming rules of the database.
 *
 * MY_ENTRY.md and MY_ENTRY/-this.md are the same object, "(lnk)" in a name
 * marks a link stub and a name is otherwise taken verbatim (plan §2.2).
 */
class ObjPath
{
  private Db $db;
  private string $thisFile;
  private string $linkMarker;

  // Characters Windows and Linux refuse in a file name
  private const FORBIDDEN = ['\\', '/', ':', '*', '?', '"', '<', '>', '|'];

  public function __construct( Db $db, Config $config )
  {
    $this->db         = $db;
    $this->thisFile   = $config->db('thisFile', '-this');
    $this->linkMarker = $config->db('linkMarker', ' (lnk)');
  }

  public function thisFileName() : string
  {
    return "{$this->thisFile}.md";
  }

  public function isThisFile( string $name ) : bool
  {
    return $name === $this->thisFileName();
  }

  /**
   * The file holding an object, or null for a folder without a -this file.
   */
  public function fileOf( string $rel ) : ?string
  {
    if( $rel === '' )
      return null;

    if( is_dir($this->db->abs($rel)))
    {
      $file = "{$rel}/{$this->thisFileName()}";
      return is_file($this->db->abs($file)) ? $file : null;
    }

    // Resources keep their extension, so the path is already the file. This is
    // checked first, otherwise a sidecar would be mistaken for the object.
    if( is_file($this->db->abs($rel)))
      return $rel;

    return is_file($this->db->abs("{$rel}.md")) ? "{$rel}.md" : null;
  }

  public function isContainer( string $rel ) : bool
  {
    return $rel === '' || is_dir($this->db->abs($rel));
  }

  public function isLinkName( string $name ) : bool
  {
    return $this->linkMarker !== '' && strpos($name, $this->linkMarker) !== false;
  }

  public function stripMarker( string $name ) : string
  {
    if( $this->linkMarker === '' )
      return $name;

    return trim(str_replace($this->linkMarker, '', $name));
  }

  public function linkName( string $title ) : string
  {
    return $this->sanitize($title) . $this->linkMarker;
  }

  /**
   * Names are taken verbatim, only what the file system refuses is replaced.
   */
  public function sanitize( string $title ) : string
  {
    $name = str_replace(self::FORBIDDEN, '-', trim($title));
    $name = preg_replace('/\s+/u', ' ', $name);
    $name = trim($name, ' .');

    return $name === '' ? 'Untitled' : $name;
  }

  /**
   * Free name in a folder, adding " 2", " 3", ... when taken.
   */
  public function freeName( string $parentRel, string $name, string $suffix = '.md' ) : string
  {
    $try = $name;
    $n   = 1;

    while( $this->nameTaken($parentRel, $try, $suffix))
    {
      $n++;
      $try = "{$name} {$n}";
    }

    return $try;
  }

  private function nameTaken( string $parentRel, string $name, string $suffix ) : bool
  {
    $base = $parentRel === '' ? $name : "{$parentRel}/{$name}";

    return is_dir($this->db->abs($base)) || is_file($this->db->abs($base . $suffix));
  }

  /**
   * Object path of a directory entry, or null when the entry is not an object
   * of its own (hidden file, -this file, sidecar of a resource).
   */
  public function pathOfEntry( string $parentRel, string $entry ) : ?string
  {
    if( $entry === '.' || $entry === '..' || strpos($entry, '.') === 0 )
      return null;

    if( $this->isThisFile($entry))
      return null;

    $base = $parentRel === '' ? $entry : "{$parentRel}/{$entry}";

    if( is_dir($this->db->abs($base)))
      return $base;

    if( substr($entry, -3) === '.md' )
    {
      $stem = substr($base, 0, -3);

      // "slides.pdf.md" next to "slides.pdf" is a sidecar, not an object
      if( is_file($this->db->abs($stem)))
        return null;

      // "X.md" next to a folder "X" is shadowed by the folder
      if( is_dir($this->db->abs($stem)))
        return null;

      return $stem;
    }

    return $base;
  }

  /**
   * Sidecar file of a resource, existing or not.
   */
  public function sidecarOf( string $rel ) : string
  {
    return "{$rel}.md";
  }
}
