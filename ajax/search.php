<?php

/**
 * The search overlay: ranked matches for a query, the recent list without
 * one. Both come back as the same rows, so the overlay never has to know
 * which of the two it is showing.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Search;
use Sys\View;

$query = (string) $ajax->param('q', '');

if( $query === '' )
{
  $hits      = array_slice($app->recent->objs(), 0, 8);
  $heading   = 'Recent';
  $emptyHint = 'Nothing opened yet. Start typing to search.';
}
else
{
  $hits      = (new Search($app))->run($query);
  $heading   = '';
  $emptyHint = 'Nothing matches.';
}

return [
  'query' => $query,
  'total' => count($hits),
  'html'  => View::render("{$app->root}/view/search.php", [
    'app'       => $app,
    'icons'     => $app->asset('styles/icons.svg'),
    'hits'      => $hits,
    'heading'   => $heading,
    'emptyHint' => $emptyHint
  ])
];
