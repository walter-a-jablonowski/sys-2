<?php

namespace Sys;

/**
 * The "..." dropdown: entries the viewer brings along, a divider, then the
 * entries every object has.
 */
class Menu
{
  public static function build( App $app, Obj $obj ) : array
  {
    $viewer  = $app->viewers->forObj($obj);
    $entries = [];

    // Type and viewer name the folder an ajax entry is answered from
    foreach( $viewer->menu() as $entry )
    {
      $entries[] = [
        'id'      => $entry['id'] ?? '',
        'label'   => $entry['label'] ?? ($entry['id'] ?? ''),
        'ajax'    => $entry['ajax'] ?? '',
        'type'    => $viewer->type->name,
        'viewer'  => $viewer->name,
        'js'      => $entry['js'] ?? '',
        'confirm' => (bool) ($entry['confirm'] ?? false)
      ];
    }

    if( $entries )
      $entries[] = ['divider' => true];

    // A link pins its target, a broken one has nothing to pin. An object without id gets one when pinned (Index::ensureId)
    $pins = $obj->isLink ? $obj->linkTarget ?? $app->index->target($obj) : $obj;

    if( $pins !== null )
      $entries[] = ['id' => 'pin', 'label' => $app->pinned->has($pins->id) ? 'Unpin' : 'Pin'];

    $entries[] = ['id' => 'rename', 'label' => 'Rename'];
    $entries[] = ['id' => 'move',   'label' => 'Move to…'];

    if( ! $obj->isLink )
      $entries[] = ['id' => 'link', 'label' => 'Link into…'];

    $entries[] = ['divider' => true];
    $entries[] = ['id' => 'reveal', 'label' => 'Show path'];
    $entries[] = ['id' => 'health', 'label' => 'Check database'];
    $entries[] = ['id' => 'delete', 'label' => 'Delete', 'danger' => true];

    return $entries;
  }
}
