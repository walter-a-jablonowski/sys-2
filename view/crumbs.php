<?php

/**
 * The way from the root down to the current container, in the header of the
 * navigation column.
 *
 * A segment leads to its own container. The chevron in front of a segment
 * opens the containers of the level above it, which is the sideways jump.
 * A deep trail keeps the root and the last two segments only, everything
 * between them moves into the "…" and its dropdown.
 *
 * @var Sys\App   $app
 * @var Sys\Obj[] $trail   root first, the current container last
 * @var string    $icons
 */

$count  = count($trail);
$hidden = $count > 4 ? array_slice($trail, 1, $count - 3) : [];
$slots  = [['obj' => $trail[0], 'skipped' => []]];

// The "…" stands in for the first level it hides, so the chevron in front
// of it keeps pointing at the level above, like every other one

if( $hidden )
  $slots[] = ['obj' => $hidden[0], 'skipped' => $hidden];

foreach( array_slice($trail, $hidden ? -2 : 1) as $obj )
  $slots[] = ['obj' => $obj, 'skipped' => []];

$last = count($slots) - 1;

foreach( $slots as $i => $slot ):

  if( $i > 0 ):
?>
  <button type="button" class="crumb-sep" data-action="crumbLevel" title="Show this level"
          data-path="<?= htmlspecialchars($slot['obj']->parentPath(), ENT_QUOTES) ?>">
    <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#chevron"></use></svg>
  </button>
<?php
  endif;

  if( $slot['skipped'] ):

    $skipped = array_map(function( Sys\Obj $obj ) {

      return ['path' => $obj->path, 'title' => $obj->title];
    }, $slot['skipped']);
?>
  <button type="button" class="crumb crumb-more" data-action="crumbSkipped" title="Levels in between"
          data-skipped="<?= htmlspecialchars(json_encode($skipped, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>">…</button>
<?php
  elseif( $i === $last ):
?>
  <span class="crumb is-current"><?= htmlspecialchars($slot['obj']->title, ENT_QUOTES) ?></span>
<?php
  else:
?>
  <button type="button" class="crumb" data-action="goPath"
          data-path="<?= htmlspecialchars($slot['obj']->path, ENT_QUOTES) ?>"><?= htmlspecialchars($slot['obj']->title, ENT_QUOTES) ?></button>
<?php
  endif;

endforeach;
