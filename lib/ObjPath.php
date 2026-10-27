<?php

namespace Sys;

/**
 * Naming rules of the database.
 *
 * MY_ENTRY.md and MY_ENTRY/-this.md are the same object, "(lnk)" in a name
 * marks a link stub and a name is otherwise taken verbatim (plan §2.2).
 *
 * The content file of a folder is written as "thisFile" and recognised by
 * "thisFileMatch" too, e.g. "-\s*DESC.*" accepts "- DESC.md", "-DESC notes.md".
 *
 * A name may carry fields besides the title, see NameFields.
 */
class ObjPath
{
  private Db $db;
  private NameFields $fields;
  private string $thisFile;
  private string $thisPattern;
  private string $linkMarker;

  // Characters Windows and Linux refuse in a file name
  private const FORBIDDEN = ['\\', '/', ':', '*', '?', '"', '<', '>', '|'];

  public function __construct( Db $db, Config $config )
  {
    $this->db         = $db;
    $this->fields     = new NameFields((array) $config->db('nameFields', []));
    $this->thisFile   = $config->db('thisFile', '-this');
    $this->linkMarker = $config->db('linkMarker', ' (lnk)');

    // The written name always matches, so a file the app made is found again
    $match = preg_quote($this->thisFile, '~');

    if( (string) $config->db('thisFileMatch', '') !== '' )
      $match .= '|' . $config->db('thisFileMatch');

    $this->thisPattern = "~^(?:{$match})\.md$~u";
  }

  public function thisFileName() : string
  {
    return "{$this->thisFile}.md";
  }

  public function isThisFile( string $name ) : bool
  {
    return preg_match($this->thisPattern, $name) === 1;
  }

  /**
   * Names of the content files in a folder, alphabetical. More than one is
   * a mistake on disk: the first wins, the check reports the others.
   *
   * @return string[]
   */
  public function thisFilesIn( string $rel ) : array
  {
    $abs = $this->db->abs($rel);

    return array_values(array_filter(scandir($abs), fn( $entry ) => $this->isThisFile($entry) && is_file("{$abs}/{$entry}")));
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
      $names = $this->thisFilesIn($rel);
      return $names ? "{$rel}/{$names[0]}" : null;
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

  public function fields() : NameFields
  {
    return $this->fields;
  }

  /**
   * Title and fields a name carries. A resource keeps its extension in the
   * title, its fields come from the part before it. A name holding only
   * fields has an empty title, also for a resource.
   *
   * @return array{title: string, data: array, tokens: array}
   */
  public function parseName( string $name, bool $isResource = false ) : array
  {
    [$stem, $ext] = $isResource ? $this->splitExt($name) : [$name, ''];

    $parsed = $this->fields->parse($this->stripMarker($stem));

    if( $parsed['title'] !== '' )
      $parsed['title'] .= $ext;

    return $parsed;
  }

  /**
   * The name an object gets for a new title and new field values, fields not
   * given keep their tokens as written. A title is required besides the
   * fields. What is written has to read back the same, otherwise the title
   * would swallow a field or the other way round.
   */
  public function nameFor( Obj $obj, string $title, array $values = [] ) : string
  {
    $tokens = $this->parseName($obj->name(), $obj->isResource)['tokens'];

    foreach( $values as $field => $value )
      $tokens[$field] = $this->fields->format($field, $value);

    [$stem, $ext] = $obj->isResource ? $this->splitExt($title) : [$title, ''];

    if( trim($stem, ' .') === '' )
      throw Failure::invalid('A title is required besides the fields in the name', ['field' => 'title']);

    $name = $this->sanitize(implode(' ', array_merge([$stem], $this->fields->flatten($tokens)))) . $ext;

    if( $this->isLinkName($obj->name()))
      $name .= $this->linkMarker;

    $back   = $this->parseName($name, $obj->isResource);
    $expect = array_filter(array_replace($obj->nameData, $values), fn( $value ) => $value !== '' && $value !== []);
    $wrong  = array_filter(array_keys($back['data'] + $expect), fn( $field ) => ($back['data'][$field] ?? null) !== ($expect[$field] ?? null));

    // An edited value that does not read back is its own fault, anything else the title's
    $edited = array_values(array_intersect($wrong, array_keys($values)));

    if( $edited )
      throw Failure::invalid("{$this->fields->rules()[$edited[0]]['label']} cannot be written into the name like this", ['field' => $edited[0]]);

    if( $wrong || $back['title'] !== $this->sanitize($title))
      throw Failure::invalid('Part of the title would be read as a field of the name', ['field' => 'title']);

    return $name;
  }

  /**
   * A path as one readable line of titles. The empty path is the database
   * itself, which is what sits at the top of every trail.
   */
  public function label( string $rel ) : string
  {
    if( $rel === '' )
      return $this->db->name();

    return implode(' › ', array_map(fn( $segment ) => $this->parseName($segment)['title'], explode('/', $rel)));
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
    // Such an entry would vanish into its folder's content
    if( $suffix === '.md' && $this->isThisFile("{$name}.md"))
      throw Failure::invalid("\"{$name}\" is reserved for the content of a folder");

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

  /**
   * "slides.pdf" -> ["slides", ".pdf"], ".pdf" -> ["", ".pdf"] (a title
   * missing), a name without extension keeps it all.
   */
  private function splitExt( string $name ) : array
  {
    $dot = strrpos($name, '.');

    return $dot !== false ? [substr($name, 0, $dot), substr($name, $dot)] : [$name, ''];
  }
}
