<?php /* $d = data */ ?>
<div class="card mb-2">
  <div class="card-body">
    <h5 class="card-title mb-1"><?= htmlspecialchars((string)($d['name'] ?? '')) ?></h5>
    <div class="text-muted small mb-2">Time: <?= htmlspecialchars((string)($d['time'] ?? '')) ?></div>
    <?php if( trim((string)($d['description'] ?? '')) !== '' ) : ?>
      <div class="card-text"><?= nl2br(htmlspecialchars((string)$d['description'])) ?></div>
    <?php endif ?>
  </div>
</div>
