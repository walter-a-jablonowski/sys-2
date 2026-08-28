<?php

/**
 * One level of the hierarchy.
 *
 * Serves the picker dialog, for a move target (containers only) or a link
 * target (objects of the types a tab accepts), and the breadcrumb, which
 * asks for containers and keeps the folders of the answer.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

$container = $ajax->obj();
$types     = $ajax->arrayParam('types');
$pickable  = (string) $ajax->param('pick', 'container');
$exclude   = (string) $ajax->param('exclude', '');

$items = [];

foreach( $app->store->children($container->path) as $child )
{
  if( $child->isLink || $child->isResource )
    continue;

  if( $exclude !== '' && ($child->path === $exclude || strpos("{$child->path}/", "{$exclude}/") === 0))
    continue;

  $matches = ! $types || in_array($child->type, $types, true);

  if( $pickable === 'container' && ! $child->isFolder && ! $matches )
    continue;

  $items[] = [
    'path'      => $child->path,
    'title'     => $child->title,
    'type'      => $child->type,
    'id'        => $child->id,
    'isFolder'  => $child->isFolder,
    'pickable'  => $pickable === 'container' ? true : $matches
  ];
}

usort($items, function( array $a, array $b ) {

  return [$b['isFolder'], $a['title']] <=> [$a['isFolder'], $b['title']];
});

return [
  'path'   => $container->path,
  'title'  => $container->path === '' ? $app->db->name() : $container->title,
  'parent' => $container->parentPath(),
  'isRoot' => $container->path === '',
  'items'  => $items
];
