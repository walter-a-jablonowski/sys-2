<?php

/**
 * The fixed values in an object's name, e.g. its tags or its prio, as small
 * chips: on every card and at the head of the navigation column, so a type's
 * own renderers need not care.
 *
 * A value lists are sorted by (e.g. a priority) looks quieter than a tag,
 * a selected one stands out.
 *
 * @var Sys\App $app
 * @var Sys\Obj $obj
 */

foreach( $app->path->fields($obj->type)->fixedValues($obj->nameData) as $tag ):

  $class = 'tag' . ($tag['sort'] ? ' tag-rank' : '') . ($tag['selected'] !== '' ? ' tag-selected' : '');
?>
  <span class="<?= $class ?>" data-field="<?= htmlspecialchars($tag['field'], ENT_QUOTES) ?>"
        data-value="<?= htmlspecialchars($tag['value'], ENT_QUOTES) ?>"
        title="<?= htmlspecialchars($tag['value'], ENT_QUOTES) ?>"><?= htmlspecialchars($tag['label'], ENT_QUOTES) ?></span>
<?php
endforeach;
