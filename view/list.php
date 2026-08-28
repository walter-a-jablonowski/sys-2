<?php

/**
 * The rendered children of one tab. Used for the first paint and for every
 * later ajax update, so both go through exactly the same renderers.
 *
 * @var Sys\App $app
 * @var array   $result   result of ChildQuery::run()
 * @var int     $offset
 * @var string  $mode     'list' or 'mobile'
 * @var bool    $showPath   name the container each entry lives in
 * @var string  $emptyHint  what to say when the list has nothing to show
 */

$showPath  = $showPath ?? false;
$emptyHint = $emptyHint ?? 'Nothing here yet.';

if( ! $result['items'] ):
?>
  <div class="empty-hint">
    <?= $filter ?? '' ? 'Nothing matches the filter.' : $emptyHint ?>
  </div>
<?php
else:

  foreach( $result['items'] as $child )
  {
    $renderer = $app->renderer($mode, $child);

    if( $showPath )
      $renderer->withContext($child->parentPath());

    $renderer->render();
  }

  if( $result['hasMore'] ):
?>
    <button type="button" class="load-more" data-offset="<?= $offset + count($result['items']) ?>">
      Load more
      <span class="load-more-rest"><?= $result['total'] - $offset - count($result['items']) ?> left</span>
    </button>
<?php
  endif;
endif;
