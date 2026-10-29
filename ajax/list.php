<?php

/**
 * One page of the navigation list: either the children of a container, or
 * one of the saved lists that reach across the whole hierarchy.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\ChildQuery;
use Sys\Obj;
use Sys\View;

$source = (string) $ajax->param('source', 'children');
$offset = $ajax->intParam('offset');
$filter = (string) $ajax->param('filter', '');
$mode   = $ajax->param('mode', 'list') === 'mobile' ? 'mobile' : 'list';
$limit  = (int) $app->config->db('pageSize', 50);

if( ! in_array($source, ['children', 'recent', 'pinned', 'selected'], true))
  $source = 'children';

$hints = [
  'children' => 'Nothing here yet.',
  'recent'   => 'Nothing opened yet.',
  'pinned'   => 'Nothing pinned yet. Pin an entry from its menu.',
  'selected' => 'Nothing selected for the whole database yet.'
];

if( $source === 'children' )
{
  $container = $ajax->obj();
  $query     = new ChildQuery($app);
  $tabs      = $query->tabs($container);
  $tab       = (string) $ajax->param('tab', '');

  if( ! isset($tabs[$tab]))
    $tab = (string) array_key_first($tabs);

  // One page beyond the offset, the client appends what it gets
  $result = $query->run($container->path, $tabs[$tab], $offset, null, $filter);
  $path   = $container->path;
}
else
{
  // The selected ones are found by their names, wherever they live, and sorted like any list
  if( $source === 'selected' )
  {
    $items = array_values(array_filter(array_map([$app->store, 'get'], $app->index->selected())));
    (new ChildQuery($app))->sort($items);
  }
  else
    $items = ($source === 'pinned' ? $app->pinned : $app->recent)->objs();

  $path = '';
  $tab  = '';

  if( $filter !== '' )
  {
    $items = array_values(array_filter($items, function( Obj $obj ) use ( $filter ) {

      return mb_stripos($obj->title, $filter) !== false;
    }));
  }

  $result = [
    'items'   => array_slice($items, $offset, $limit),
    'total'   => count($items),
    'hasMore' => $offset + $limit < count($items)
  ];
}

return [
  'path'    => $path,
  'tab'     => $tab,
  'source'  => $source,
  'total'   => $result['total'],
  'hasMore' => $result['hasMore'],
  'html'    => View::render("{$app->root}/view/list.php", [
    'app'       => $app,
    'result'    => $result,
    'offset'    => $offset,
    'filter'    => $filter,
    'mode'      => $mode,
    'showPath'  => $source !== 'children',
    'emptyHint' => $hints[$source]
  ])
];
