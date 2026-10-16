<?php

namespace Sys;

/**
 * Discovers the type definitions (types/MY_TYPE.yml) and hands out Type objects.
 */
class TypeRegistry
{
  private string $dir;
  private string $defaultType;
  private array $types = [];
  private bool $scanned = false;

  public function __construct( string $dir, Config $config )
  {
    $this->dir         = rtrim(str_replace('\\', '/', $dir), '/');
    $this->defaultType = $config->db('defaultType', 'Info');
  }

  /**
   * An unknown type falls back to the default one, the value in the file is
   * left untouched so nothing is lost (plan §1 C12).
   */
  public function get( string $name ) : Type
  {
    $this->scan();

    return $this->types[$name] ?? $this->types[$this->defaultType];
  }

  public function has( string $name ) : bool
  {
    $this->scan();

    return isset($this->types[$name]);
  }

  /**
   * @return Type[]
   */
  public function all() : array
  {
    $this->scan();

    return $this->types;
  }

  public function forObj( Obj $obj ) : Type
  {
    return $this->get($obj->type);
  }

  private function scan() : void
  {
    if( $this->scanned )
      return;

    $this->scanned = true;

    foreach( scandir($this->dir) as $entry )
    {
      if( strpos($entry, '.') === 0 || pathinfo($entry, PATHINFO_EXTENSION) !== 'yml' )
        continue;

      $name = pathinfo($entry, PATHINFO_FILENAME);

      $this->types[$name] = new Type($name, Config::read("{$this->dir}/{$entry}"));
    }

    if( ! isset($this->types[$this->defaultType]))
      $this->types[$this->defaultType] = new Type($this->defaultType, []);
  }
}
