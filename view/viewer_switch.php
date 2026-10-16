<?php

/**
 * Viewer switch in the header, next to the object's name. Only shown when
 * the type brings more than one viewer, picking one is stored for the user.
 *
 * @var Sys\App    $app
 * @var Sys\Viewer $viewer   the one currently shown
 */

$viewers = $app->viewers->all($viewer->type);

if( count($viewers) < 2 )
  return;

$entries = [];

foreach( $viewers as $option )
  $entries[] = ['id' => $option->name, 'label' => $option->label(), 'current' => $option === $viewer];

?>
<button type="button" class="btn-quiet head-viewer" data-action="viewerMenu" title="Switch viewer"
        data-type="<?= htmlspecialchars($viewer->type->name, ENT_QUOTES) ?>"
        data-viewers="<?= htmlspecialchars(json_encode($entries), ENT_QUOTES) ?>">
  <?= htmlspecialchars($viewer->label(), ENT_QUOTES) ?>
  <svg class="icon" aria-hidden="true"><use href="<?= htmlspecialchars($app->asset('styles/icons.svg'), ENT_QUOTES) ?>#chevron-down"></use></svg>
</button>
