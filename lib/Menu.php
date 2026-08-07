<?php

namespace Sys;

/**
 * The "..." dropdown: entries the type brings along, a divider, then the
 * entries every object has.
 */
class Menu
{
  public static function build( App $app, Obj $obj ) : array
  {
    $entries = [];

    foreach( $app->types->forObj($obj)->menu() as $entry )
    {
      $entries[] = [
        'id'      => $entry['id'] ?? '',
        'label'   => $entry['label'] ?? ($entry['id'] ?? ''),
        'ajax'    => $entry['ajax'] ?? '',
        'js'      => $entry['js'] ?? '',
        'confirm' => (bool) ($entry['confirm'] ?? false)
      ];
    }

    if( $entries )
      $entries[] = ['divider' => true];

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
