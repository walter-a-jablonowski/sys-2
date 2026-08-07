<?php

/**
 * Health report of the database: shadowed files, duplicate ids, broken links.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

$app->index->build();

return [
  'db'      => $app->db->name(),
  'objects' => count($app->index->all()),
  'issues'  => $app->db->check($app->store, $app->index)
];
