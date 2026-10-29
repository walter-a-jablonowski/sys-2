<?php

/**
 * Health report of the database: shadowed files, extra content files, duplicate ids,
 * names without a title, broken links, missing required fields.
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

$app->index->build();

return [
  'db'      => $app->db->name(),
  'objects' => count($app->index->all()),
  'issues'  => $app->db->check($app->store, $app->index, $app->types)
];
