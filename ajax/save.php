<?php

/**
 * Writes the editor fields back.
 *
 * "title" and the fields carried in the name (NameFields) are the file name,
 * "body" is the markdown body, every other field is a front matter key, dots
 * making it nested (plan §2.3).
 *
 * @var Sys\App  $app
 * @var Sys\Ajax $ajax
 */

use Sys\Failure;
use Sys\Frame;

$obj    = $ajax->obj();
$loaded = $obj->type;
$fields = $ajax->arrayParam('fields');
$mtime  = $ajax->intParam('mtime') ?: null;

$title = array_key_exists('title', $fields) ? trim((string) $fields['title']) : null;
$body  = array_key_exists('body', $fields) ? (string) $fields['body'] : $obj->body;
$type  = array_key_exists('__type', $fields) ? (string) $fields['__type'] : '';

unset($fields['title'], $fields['body'], $fields['__type']);

// Fields carried in the name, only the changed ones: each one renames the file
$names      = $app->path->fields($obj->type);
$nameValues = [];

foreach( $fields as $key => $value )
{
  if( ! $names->has($key))
    continue;

  unset($fields[$key]);
  $value = $names->normalize($key, $value);

  if( $value !== $names->normalize($key, $obj->nameData[$key] ?? ''))
    $nameValues[$key] = $value;
}

$altField = $names->fieldOf('id');

if( $altField !== null && ($nameValues[$altField] ?? '') !== '' && $app->index->altPath($nameValues[$altField]) !== null )
  throw Failure::invalid("\"{$nameValues[$altField]}\" is already used by another entry", ['field' => $altField]);

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

// Nothing is saved while the title is missing, also when the name on disk holds only fields
if( ($title ?? $obj->title) === '' )
  throw Failure::invalid('A title is required besides the fields in the name', ['field' => 'title']);

// Checked before anything is written, so a stale editor cannot rename a file
if( $mtime !== null && $obj->mtime !== 0 && $obj->mtime !== $mtime )
  throw Failure::conflict('The file changed since it was loaded', ['mtime' => $obj->mtime]);

$renamed = ($title !== null && $title !== $obj->title) || $nameValues;

if( $renamed )
  $obj = $app->store->rename($obj, $title ?? $obj->title, $nameValues);

// After the rename: rename() reloads the object from disk, and a name may give another type.
// Only a type picked in the editor counts: the dropdown sends the one it was loaded with on every save
if( $type !== '' && $type !== $loaded && $app->types->has($type))
{
  $obj->type        = $type;
  $obj->typeImplied = false;    // picked, so written from now on
}

// Written for the first time: without an id the index cannot find it again
if( $obj->id === '' )
  $obj->id = $app->index->freeId($app->types->forObj($obj)->newId($obj->title));

$saved = $app->store->save($obj, $data, $body, $renamed ? null : $mtime);

$app->index->invalidate();

// The frame as well: it shows saved values, a changed type may even come with another viewer.
// A changed type also has other fields, the client then reloads the editor
return [
  'path'        => $saved->path,
  'title'       => $saved->title,
  'mtime'       => $saved->mtime,
  'typeChanged' => $saved->type !== $loaded,
  'card'        => $app->renderer($ajax->param('mode') === 'mobile' ? 'mobile' : 'list', $saved)->html()
] + Frame::build($app, $saved);
