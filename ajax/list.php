<?php

/**
 * One page of a tab: used for paging, filtering and after every change.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\ChildQuery;
use Sys\View;

$container = $ajax->obj();
$query     = new ChildQuery($app);
$tabs      = $query->tabs($container);
$tab       = (string) $ajax->param('tab', '');
$offset    = $ajax->intParam('offset');
$filter    = (string) $ajax->param('filter', '');
$mode      = $ajax->param('mode', 'list') === 'mobile' ? 'mobile' : 'list';

if( ! isset($tabs[$tab]))
  $tab = (string) array_key_first($tabs);

// One page beyond the offset, the client appends what it gets
$result = $query->run($container->path, $tabs[$tab], $offset, null, $filter);

return [
  'path'    => $container->path,
  'tab'     => $tab,
  'total'   => $result['total'],
  'hasMore' => $result['hasMore'],
  'html'    => View::render("{$app->root}/view/list.php", [
    'app'    => $app,
    'result' => $result,
    'offset' => $offset,
    'filter' => $filter,
    'mode'   => $mode
  ])
];
