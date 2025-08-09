<?php /* $d = data */ ?>
<div class="d-flex justify-content-between align-items-center w-100">
  <div class="text-muted">
    <?= htmlspecialchars(substr((string)($d['time'] ?? ''), 5, 5)) ?>
  </div>
  <div class="text-truncate">
    <span class="fw-semibold text-truncate d-inline-block" style="max-width: 70vw;">
      <?= htmlspecialchars((string)($d['name'] ?? '')) ?>
    </span>
  </div>
</div>
