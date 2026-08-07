<?php

/**
 * Writes the editor fields back.
 *
 * "title" is the file name and "body" the markdown body, every other field
 * is a front matter key, dots making it nested (plan §2.3).
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;

$obj    = $ajax->obj();
$fields = $ajax->arrayParam('fields');
$mtime  = $ajax->intParam('mtime') ?: null;

$title = array_key_exists('title', $fields) ? trim((string) $fields['title']) : null;
$body  = array_key_exists('body', $fields) ? (string) $fields['body'] : $obj->body;
$type  = array_key_exists('__type', $fields) ? (string) $fields['__type'] : '';

unset($fields['title'], $fields['body'], $fields['__type']);

// Dotted names become nested front matter, unknown keys are kept as they are
$data = $obj->data;

foreach( $fields as $name => $value )
{
  $parts = explode('.', (string) $name);
  $cursor = &$data;

  foreach( $parts as $i => $part )
  {
    if( $i === count($parts) - 1 )
    {
      $cursor[$part] = $value;
      break;
    }

    if( ! isset($cursor[$part]) || ! is_array($cursor[$part]))
      $cursor[$part] = [];

    $cursor = &$cursor[$part];
  }

  unset($cursor);
}

if( $title === '' )
  throw Failure::invalid('The title is the file name and cannot be empty', ['field' => 'title']);

// Checked before anything is written, so a stale editor cannot rename a file
if( $mtime !== null && $obj->mtime !== 0 && $obj->mtime !== $mtime )
  throw Failure::conflict('The file changed since it was loaded', ['mtime' => $obj->mtime]);

$renamed = $title !== null && $title !== $obj->title;

if( $renamed )
  $obj = $app->store->rename($obj, $title);

// After the rename: rename() reloads the object from disk
if( $type !== '' && $type !== $obj->type && $app->types->has($type))
  $obj->type = $type;

$saved = $app->store->save($obj, $data, $body, $renamed ? null : $mtime);

$app->index->invalidate();

return [
  'path'   => $saved->path,
  'title'  => $saved->title,
  'mtime'  => $saved->mtime,
  'card'   => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $saved)->html(),
  'header' => $app->renderer('header', $saved)->html(),
  'footer' => $app->types->forObj($saved)->get('footer', false) ? $app->renderer('footer', $saved)->html() : ''
];
