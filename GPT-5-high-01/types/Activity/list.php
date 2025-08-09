<?php /* $d = data */ ?>
<div class="d-flex justify-content-between align-items-center w-100">
  <div class="text-truncate">
    <span class="badge bg-primary me-2"><?= htmlspecialchars((string)($d['priority'] ?? '')) ?></span>
    <span class="fw-semibold text-truncate d-inline-block" style="max-width: 70vw;">
      <?= htmlspecialchars((string)($d['name'] ?? '')) ?>
    </span>
  </div>
  <div>
    <span class="badge bg-secondary text-uppercase">
      <?= htmlspecialchars((string)($d['state'] ?? '')) ?>
    </span>
  </div>
</div>
