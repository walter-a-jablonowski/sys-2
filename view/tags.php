<?php

/**
 * The fixed values in an object's name, e.g. its tags or its prio, as small
 * chips: on every card and at the head of the navigation column, so a type's
 * own renderers need not care.
 *
 * Only those on one side of the title: leading the name ($start) or not.
 *
 * Each has the color its field gives the value (NameFields "colors"), the
 * accent otherwise; a selected one is filled, so it stands out. A chip shows
 * the value as the name writes it (e.g. the prio "2"), its label is the tooltip.
 *
 * @var Sys\App $app
 * @var Sys\Obj $obj
 * @var bool    $start
 */

foreach( $app->path->fields($obj->type)->fixedValues($obj->nameData) as $tag ):

  if( $tag['start'] !== $start )
    continue;

  $class = 'tag' . ($tag['color'] !== '' ? " tag-{$tag['color']}" : '') . ($tag['selected'] !== '' ? ' tag-selected' : '');
?>
  <span class="<?= $class ?>" data-field="<?= htmlspecialchars($tag['field'], ENT_QUOTES) ?>"
        data-value="<?= htmlspecialchars($tag['value'], ENT_QUOTES) ?>"
        title="<?= htmlspecialchars($tag['label'], ENT_QUOTES) ?>"><?= htmlspecialchars($tag['value'], ENT_QUOTES) ?></span>
<?php
endforeach;
