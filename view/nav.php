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

<div class="list" data-path="<?= htmlspecialchars($container->path, ENT_QUOTES) ?>" data-tab="<?= htmlspecialchars($tab, ENT_QUOTES) ?>">
  <?php require __DIR__ . '/list.php' ?>
</div>
