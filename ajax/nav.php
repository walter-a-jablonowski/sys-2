<?php

/**
 * Everything the navigation column needs for a container: tabs, list, state.
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
$mode      = $ajax->param('mode', 'list') === 'mobile' ? 'mobile' : 'list';

if( ! isset($tabs[$tab]))
  $tab = (string) array_key_first($tabs);

$result = $query->run($container->path, $tabs[$tab]);

return [
  'path'   => $container->path,
  'title'  => $container->title,
  'type'   => $container->type,
  'tab'    => $tab,
  'parent' => $container->parentPath(),
  'isRoot' => $container->path === '',
  'total'  => $result['total'],
  'create' => $app->types->forObj($container)->createOptions($tab),
  'html'   => View::render("{$app->root}/view/nav.php", [
    'app'       => $app,
    'icons'     => $app->asset('styles/icons.svg'),
    'container' => $container,
    'tabs'      => $tabs,
    'tab'       => $tab,
    'result'    => $result,
    'offset'    => 0,
    'filter'    => '',
    'mode'      => $mode
  ])
];
