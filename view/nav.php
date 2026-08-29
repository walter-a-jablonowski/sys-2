<?php

/**
 * Content of the navigation column: tabs, tools and the list.
 *
 * @var Sys\App $app
 * @var Sys\Obj $container
 * @var array   $tabs
 * @var string  $tab
 * @var array   $result
 * @var string  $mode
 */

$type    = $app->types->forObj($container);
$offset  = 0;
$filter  = '';
$current = $tabs[$tab] ?? reset($tabs);

?>
<div class="row row-tabs">
  <div class="tabs" role="tablist">
    <?php foreach( $tabs as $id => $def ): ?>
      <button type="button" class="tab<?= $id === $tab ? ' is-active' : '' ?>" data-tab="<?= htmlspecialchars($id, ENT_QUOTES) ?>" role="tab">
        <?= htmlspecialchars($def['label'] ?? ucfirst($id), ENT_QUOTES) ?>
      </button>
    <?php endforeach ?>
  </div>
  <?php $options = $type->createOptions($tab) ?>
  <button type="button" class="icon-btn add" data-action="add" title="Add"<?= $options ? '' : ' disabled' ?>>
    <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#plus"></use></svg>
  </button>
</div>

<?php if( $current['tools'] ?? false ): ?>
  <div class="row row-tools">
    <label class="filter">
      <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#search"></use></svg>
      <input type="search" class="filter-input" placeholder="Filter" aria-label="Filter entries">
    </label>
  </div>
<?php endif ?>

<?php
  /*
    The container itself, at the head of its own list, so what you navigated
    to can be opened again. Only where the list shows its entries: the other
    tabs have a purpose of their own, and the root names no single object.
  */
?>
<?php if( $tab === 'entries' && $container->path !== '' ): ?>
  <button type="button" class="self" data-role="self" data-action="editContainer"
          data-path="<?= htmlspecialchars($container->path, ENT_QUOTES) ?>" title="Open this entry">
    <span class="self-icon">
      <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#<?= htmlspecialchars($type->icon(), ENT_QUOTES) ?>"></use></svg>
    </span>
    <span class="self-title"><?= htmlspecialchars($container->title, ENT_QUOTES) ?></span>
    <span class="self-type"><?= htmlspecialchars($type->label(), ENT_QUOTES) ?></span>
  </button>
<?php endif ?>

<div class="list" data-path="<?= htmlspecialchars($container->path, ENT_QUOTES) ?>" data-tab="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
  <?php require __DIR__ . '/list.php' ?>
</div>

<?php
  /*
    Sources of the list. Recent and pinned reach across the whole tree, so
    they only make sense where the list shows entries: 'entries' is the tab
    id every type uses for that. Arriving at a container always means main,
    which is why no active source has to be passed in here.
  */
?>
<?php if( $tab === 'entries' ): ?>
  <div class="row-modes">
    <button type="button" class="pill is-active" data-source="children">Main</button>
    <button type="button" class="pill" data-source="recent">Recent</button>
    <button type="button" class="pill" data-source="pinned">Pinned</button>
  </div>
<?php endif ?>
