<?php /* $d = data */ ?>
<div class="w-100">
  <div class="d-flex justify-content-between align-items-center w-100">
    <div class="text-truncate">
      <?php if( trim((string)($d['url'] ?? '')) !== '' ) : ?>
        <a href="<?= htmlspecialchars((string)$d['url']) ?>" target="_blank" class="fw-semibold text-truncate d-inline-block" style="max-width: 65vw;">
          <?= htmlspecialchars((string)($d['name'] ?? '')) ?>
        </a>
      <?php else : ?>
        <span class="fw-semibold text-truncate d-inline-block" style="max-width: 65vw;">
          <?= htmlspecialchars((string)($d['name'] ?? '')) ?>
        </span>
      <?php endif ?>
    </div>
    <div>
      <span class="badge bg-info text-dark text-uppercase">
        <?= htmlspecialchars((string)($d['state'] ?? '')) ?>
      </span>
    </div>
  </div>
  <div class="d-flex justify-content-between align-items-center w-100 mt-1 small text-muted">
    <div><?= htmlspecialchars(substr((string)($d['time'] ?? ''), 0, 10)) ?></div>
    <div>#<?= htmlspecialchars((string)($d['files_nr'] ?? '')) ?></div>
  </div>
</div>
