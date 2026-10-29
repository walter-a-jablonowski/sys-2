<?php

namespace Sys;

/**
 * Children of a container as one tab shows them: filtered by type, links
 * resolved, sorted newest first and cut into pages.
 */
class ChildQuery
{
  private App $app;

  public function __construct( App $app )
  {
    $this->app = $app;
  }

  /**
   * @return array{items: Obj[], total: int, hasMore: bool}
   */
  public function run( string $path, array $tab, int $offset = 0, ?int $limit = null, string $filter = '' ) : array
  {
    $limit   = $limit ?? (int) $this->app->config->db('pageSize', 50);
    $include = $tab['include'] ?? ['inline', 'link'];
    $types   = $tab['types'] ?? [];
    $groups  = array_fill_keys($include, []);

    foreach( $this->app->store->children($path) as $child )
    {
      $this->resolveLink($child);

      $group = $child->isLink ? 'link' : 'inline';

      if( ! isset($groups[$group]))
        continue;

      if( ! $this->matchesType($child, $types))
        continue;

      if( ! $this->matchesFilter($child, $filter))
        continue;

      $groups[$group][] = $child;
    }

    // The order of "include" decides which group is listed first
    $items = [];

    foreach( $include as $group )
    {
      $this->sort($groups[$group]);
      $items = array_merge($items, $groups[$group]);
    }

    $total = count($items);

    return [
      'items'   => array_slice($items, $offset, $limit),
      'total'   => $total,
      'hasMore' => $offset + $limit < $total
    ];
  }

  /**
   * Tabs available on a container, always at least one.
   */
  public function tabs( Obj $container ) : array
  {
    $tabs = $this->app->types->forObj($container)->tabs();

    return $tabs ?: ['entries' => ['label' => 'Entries', 'include' => ['inline', 'link'], 'tools' => true]];
  }

  // Internals

  private function resolveLink( Obj $obj ) : void
  {
    if( $obj->isLink )
      $obj->linkTarget = $this->app->index->target($obj);
  }

  /**
   * A link is matched by the type of its target.
   *
   * A tab without a type list takes everything but resources: those have a
   * tab of their own, listing them twice would only be noise.
   */
  private function matchesType( Obj $obj, array $types ) : bool
  {
    if( ! $types || in_array('*', $types, true))
      return $obj->display()->type !== 'File';

    return in_array($obj->display()->type, $types, true);
  }

  /**
   * The title or one of the fixed values in the name, so "best" lists
   * everything tagged so.
   */
  private function matchesFilter( Obj $obj, string $filter ) : bool
  {
    if( $filter === '' || mb_stripos($obj->title, $filter) !== false )
      return true;

    foreach( $this->app->path->fields($obj->type)->fixedValues($obj->nameData) as $tag )
    {
      if( mb_stripos($tag['value'], $filter) !== false || mb_stripos($tag['label'], $filter) !== false )
        return true;
    }

    return false;
  }

  /**
   * Newest first, from the front matter timestamps (plan §2.7). A field in
   * the name may come before that, e.g. the prio: selected entries first,
   * then by priority, entries without one last (NameFields "sort"). Also
   * used for lists that reach across the tree.
   */
  public function sort( array &$items ) : void
  {
    $desc = strtolower((string) ($this->app->config->db('sort')['dir'] ?? 'desc')) === 'desc';

    usort($items, function( Obj $a, Obj $b ) use ( $desc ) {

      $rank = $this->rank($a) <=> $this->rank($b);

      if( $rank !== 0 )
        return $rank;

      $cmp = $this->stamp($a) <=> $this->stamp($b);

      if( $cmp === 0 )
        return strnatcasecmp($a->title, $b->title);

      return $desc ? -$cmp : $cmp;
    });
  }

  private function rank( Obj $obj ) : int
  {
    return $this->app->path->fields($obj->type)->rank($obj->nameData);
  }

  private function stamp( Obj $obj ) : int
  {
    foreach( [$obj->created, $obj->modified] as $iso )
    {
      if( $iso !== '' && ($time = strtotime($iso)) !== false )
        return $time;
    }

    return $obj->mtime;
  }
}
