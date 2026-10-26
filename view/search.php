<?php

/**
 * Rows of the search overlay: what an object is called and where it lives.
 *
 * Nothing else belongs here. The overlay exists to get somewhere, so a row
 * carries only what it takes to recognise the target.
 *
 * @var Sys\App   $app
 * @var Sys\Obj[] $hits
 * @var string    $heading    names the list when it is not a search result
 * @var string    $emptyHint
 * @var string    $icons
 */

if( $heading !== '' ):
?>
  <div class="hit-head"><?= htmlspecialchars($heading, ENT_QUOTES) ?></div>
<?php
endif;

if( ! $hits ):
?>
  <div class="hit-empty"><?= htmlspecialchars($emptyHint, ENT_QUOTES) ?></div>
<?php
else:

  foreach( $hits as $obj ):
?>
  <button type="button" class="hit"
          data-path="<?= htmlspecialchars($obj->path, ENT_QUOTES) ?>"
          data-parent="<?= htmlspecialchars($obj->parentPath(), ENT_QUOTES) ?>"
          data-container="<?= $obj->isFolder ? '1' : '0' ?>">
    <span class="hit-icon">
      <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#<?= htmlspecialchars($app->types->forObj($obj)->icon(), ENT_QUOTES) ?>"></use></svg>
    </span>
    <span class="hit-body">
      <span class="hit-title"><?= htmlspecialchars($obj->title, ENT_QUOTES) ?></span>
      <span class="hit-path"><?= htmlspecialchars($app->path->label($obj->parentPath()), ENT_QUOTES) ?></span>
    </span>
  </button>
<?php
  endforeach;

endif;
