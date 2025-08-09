<?php /* $d = data */ ?>
<div class="card mb-2">
  <div class="card-body">
    <h5 class="card-title mb-1"><?= htmlspecialchars((string)($d['name'] ?? '')) ?></h5>
    <div class="text-muted small mb-2">
      <span class="me-3">Time: <?= htmlspecialchars((string)($d['time'] ?? '')) ?></span>
      <span class="me-3">Priority: <?= htmlspecialchars((string)($d['priority'] ?? '')) ?></span>
      <span class="">State: <?= htmlspecialchars((string)($d['state'] ?? '')) ?></span>
    </div>
    <?php if( trim((string)($d['description'] ?? '')) !== '' ) : ?>
      <div class="card-text"><?= nl2br(htmlspecialchars((string)$d['description'])) ?></div>
    <?php endif ?>
  </div>
</div>
