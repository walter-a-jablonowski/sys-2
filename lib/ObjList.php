<?php

namespace Sys;

/**
 * An ordered list of objects, persisted in <db>/.sys/<name>.json.
 *
 * Ids are stored instead of paths, so moving or renaming an object never
 * breaks an entry and deleting one needs no cleanup: an id the index no
 * longer knows is simply dropped when the list is read.
 */
class ObjList
{
  private Db $db;
  private Index $index;
  private string $file;
  private int $limit;
  private ?array $ids = null;

  /**
   * A limit of 0 keeps every entry, anything above cuts the tail off.
   */
  public function __construct( Db $db, Index $index, string $name, int $limit = 0 )
  {
    $this->db    = $db;
    $this->index = $index;
    $this->file  = "{$db->sysDir()}/{$name}.json";
    $this->limit = $limit;
  }

  /**
   * @return Obj[] in list order
   */
  public function objs() : array
  {
    $out = [];

    foreach( $this->ids() as $id )
    {
      $obj = $this->index->obj($id);

      if( $obj !== null )
        $out[] = $obj;
    }

    return $out;
  }

  /**
   * Moves an id to the front, whether it was in the list or not.
   */
  public function push( string $id ) : void
  {
    if( $id === '' )
      return;

    $ids = $this->without($id);

    array_unshift($ids, $id);

    if( $this->limit > 0 )
      $ids = array_slice($ids, 0, $this->limit);

    $this->save($ids);
  }

  /**
   * @return bool the state after the call: true when the id is in the list
   */
  public function toggle( string $id ) : bool
  {
    if( $id === '' )
      return false;

    $on  = ! $this->has($id);
    $ids = $this->without($id);

    if( $on )
      $ids[] = $id;

    $this->save($ids);

    return $on;
  }

  public function has( string $id ) : bool
  {
    return $id !== '' && in_array($id, $this->ids(), true);
  }

  // Internals

  private function ids() : array
  {
    if( $this->ids === null )
      $this->load();

    return $this->ids;
  }

  private function without( string $id ) : array
  {
    return array_values(array_filter($this->ids(), function( string $known ) use ( $id ) {

      return $known !== $id;
    }));
  }

  /**
   * Entries the index cannot resolve any more are left behind here, so the
   * next write cleans the file up on its own.
   */
  private function load() : void
  {
    $ids  = [];
    $data = is_file($this->file) ? json_decode((string) file_get_contents($this->file), true) : null;

    if( is_array($data))
    {
      foreach( $data as $id )
      {
        if( is_string($id) && ! in_array($id, $ids, true) && $this->index->path($id) !== null )
          $ids[] = $id;
      }
    }

    $this->ids = $ids;
  }

  private function save( array $ids ) : void
  {
    $this->ids = $ids;

    if( ! is_dir($this->db->sysDir()))
      mkdir($this->db->sysDir(), 0777, true);

    file_put_contents($this->file, json_encode($ids, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  }
}
