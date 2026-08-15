<?php

/**
 * Single page shell.
 *
 * The first paint is rendered server side so the app is usable before any
 * script runs, everything after that goes through ajax.php.
 */

require __DIR__ . '/vendor/autoload.php';

use Sys\App;
use Sys\ChildQuery;

$app = App::boot(__DIR__);

$container = $app->store->get((string) ($_GET['path'] ?? '')) ?? $app->store->get('');
$query     = new ChildQuery($app);
$tabs      = $query->tabs($container);
$tab       = (string) array_key_first($tabs);
$result    = $query->run($container->path, $tabs[$tab]);
$mode      = 'list';
$theme     = (string) $app->config->app('theme', 'default');
$icons     = $app->asset('styles/icons.svg');

?><!doctype html>
<html lang="en" data-ai="0" data-view="list">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="color-scheme" content="dark">
  <title><?= htmlspecialchars($container->title, ENT_QUOTES) ?> — <?= htmlspecialchars((string) $app->config->app('appName', 'Sys'), ENT_QUOTES) ?></title>
  <?php
    $sheets = [
      "themes/{$theme}.css",
      'styles/base.css', 'styles/layout.css', 'styles/nav.css',
      'styles/cards.css', 'styles/editor.css', 'styles/ai.css', 'styles/dialog.css'
    ];

    foreach( $app->types->all() as $type )
    {
      if( $type->hasStyles())
        $sheets[] = "types/{$type->name}/styles.css";
    }
  ?>
  <?php foreach( $sheets as $sheet ): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($app->asset($sheet), ENT_QUOTES) ?>">
  <?php endforeach ?>
</head>
<body>

<div class="app">

  <header class="band band-head">
    <div class="seg seg-nav">
      <button type="button" class="icon-btn back" data-action="back" title="Back" hidden>
        <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#back"></use></svg>
      </button>
      <span class="nav-title" data-role="containerTitle"><?= htmlspecialchars($container->title, ENT_QUOTES) ?></span>
      <button type="button" class="btn-quiet edit" data-action="editContainer" title="Open this entry">edit</button>
    </div>
    <div class="seg seg-content">
      <button type="button" class="icon-btn back-layer" data-action="backLayer" title="Back">
        <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#back"></use></svg>
      </button>
      <div class="head-slot" data-role="headerSlot">
        <span class="head-empty">No entry selected</span>
      </div>
    </div>
    <div class="head-actions">
      <button type="button" class="btn-quiet" data-action="toggleAi" title="AI sidebar">AI</button>
      <button type="button" class="icon-btn" data-action="objMenu" title="Menu" aria-haspopup="menu">
        <svg class="icon" aria-hidden="true"><use href="<?= $icons ?>#more"></use></svg>
      </button>
    </div>
  </header>

  <div class="cols">

    <nav class="col col-nav" data-role="nav">
      <?php require __DIR__ . '/view/nav.php' ?>
    </nav>

    <div class="col-resizer" data-role="navResizer" role="separator" aria-orientation="vertical" title="Drag to resize"></div>

    <main class="col col-editor" data-role="editor">
      <div class="editor-empty">
        <svg class="icon icon-big" aria-hidden="true"><use href="<?= $icons ?>#entry"></use></svg>
        <p>Select an entry on the left, or press <b>edit</b> to open this one.</p>
      </div>
    </main>

    <?php require __DIR__ . '/view/ai.php' ?>

  </div>

  <footer class="band band-foot">
    <div class="seg seg-nav">
      <span class="list-state" data-role="listState"><?= $result['total'] ?> <?= $result['total'] === 1 ? 'entry' : 'entries' ?></span>
    </div>
    <div class="seg seg-content">
      <div class="foot-slot" data-role="footerSlot"></div>
      <div class="save-state" data-role="saveState"></div>
    </div>
  </footer>

</div>

<div class="dropdown" data-role="dropdown" hidden></div>
<div class="dialog-backdrop" data-role="dialog" hidden></div>
<div class="toast" data-role="toast" hidden></div>

<script>
  window.SYS = {
    path: <?= json_encode($container->path) ?>,
    tab: <?= json_encode($tab) ?>,
    dbName: <?= json_encode($app->db->name()) ?>,
    create: <?= json_encode($app->types->forObj($container)->createOptions($tab)) ?>,
    pageSize: <?= (int) $app->config->db('pageSize', 50) ?>
  };
</script>
<script src="<?= htmlspecialchars($app->asset('controller.js'), ENT_QUOTES) ?>"></script>
<?php foreach( $app->types->all() as $type ): ?>
  <?php if( $type->hasScript()): ?>
    <script src="<?= htmlspecialchars($app->asset("types/{$type->name}/controller.js"), ENT_QUOTES) ?>"></script>
  <?php endif ?>
<?php endforeach ?>

</body>
</html>
