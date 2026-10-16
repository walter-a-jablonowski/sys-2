<?php

namespace Sys;

/**
 * Discovers the viewers of each type (type_viewer/MY_TYPE/MY_VIEWER) and
 * decides which one is shown: the user's choice first, then the one the type
 * names, then "default".
 */
class ViewerRegistry
{
  private const DIR     = 'type_viewer';
  private const DEFAULT = 'default';

  private string $root;
  private TypeRegistry $types;
  private UserSettings $user;
  private array $viewers = [];     // type name -> [viewer name -> Viewer]

  public function __construct( string $root, TypeRegistry $types, UserSettings $user )
  {
    $this->root  = $root;
    $this->types = $types;
    $this->user  = $user;
  }

  public function forObj( Obj $obj ) : Viewer
  {
    return $this->active($this->types->forObj($obj));
  }

  public function active( Type $type ) : Viewer
  {
    $viewers = $this->all($type);
    $chosen  = $this->user->get('viewers', [])[$type->name] ?? '';

    foreach( [$chosen, $type->viewer(), self::DEFAULT] as $name )
    {
      if( isset($viewers[$name]))
        return $viewers[$name];
    }

    return reset($viewers);
  }

  /**
   * Makes a viewer the one the user sees a type with.
   */
  public function choose( Type $type, string $name ) : Viewer
  {
    $viewers = $this->all($type);

    if( ! isset($viewers[$name]))
      throw Failure::notFound("viewer: {$type->name}/$name");

    $chosen = $this->user->get('viewers', []);
    $chosen[$type->name] = $name;

    $this->user->set('viewers', $chosen);

    return $viewers[$name];
  }

  /**
   * @return Viewer[]  never empty: a type without viewer folder still gets a
   *                   default viewer, which then uses the default renderers
   */
  public function all( Type $type ) : array
  {
    if( isset($this->viewers[$type->name]))
      return $this->viewers[$type->name];

    $base    = self::DIR . "/{$type->name}";
    $viewers = [];

    foreach( is_dir("{$this->root}/$base") ? scandir("{$this->root}/$base") : [] as $entry )
    {
      $path = "$base/$entry";
      $dir  = "{$this->root}/$path";

      if( strpos($entry, '.') === 0 || ! is_dir($dir))
        continue;

      $viewers[$entry] = new Viewer($entry, $type, $path, $dir, Config::read("$dir/config.yml"));
    }

    if( ! $viewers )
    {
      $path = "$base/" . self::DEFAULT;
      $viewers[self::DEFAULT] = new Viewer(self::DEFAULT, $type, $path, "{$this->root}/$path", []);
    }

    return $this->viewers[$type->name] = $viewers;
  }
}
