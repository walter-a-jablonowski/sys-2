<?php

/**
 * The rendered children of one tab. Used for the first paint and for every
 * later ajax update, so both go through exactly the same renderers.
 *
 * @var Sys\App $app
 * @var array   $result   result of ChildQuery::run()
 * @var int     $offset
 * @var string  $mode     'list' or 'mobile'
 */

if( ! $result['items'] ):
?>
  <div class="empty-hint">
    <?= $filter ?? '' ? 'Nothing matches the filter.' : 'Nothing here yet.' ?>
  </div>
<?php
else:

  foreach( $result['items'] as $child )
    $app->renderer($mode, $child)->render();

  if( $result['hasMore'] ):
?>
    <button type="button" class="load-more" data-offset="<?= $offset + count($result['items']) ?>">
      Load more
      <span class="load-more-rest"><?= $result['total'] - $offset - count($result['items']) ?> left</span>
    </button>
<?php
  endif;
endif;
