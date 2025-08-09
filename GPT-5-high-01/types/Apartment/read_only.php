<?php /* $d = data */ ?>
<div class="card mb-2">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
      <div class="me-2">
        <h5 class="card-title mb-1">
          <?php if( trim((string)($d['url'] ?? '')) !== '' ) : ?>
            <a href="<?= htmlspecialchars((string)$d['url']) ?>" target="_blank">
              <?= htmlspecialchars((string)($d['name'] ?? '')) ?>
            </a>
          <?php else : ?>
            <?= htmlspecialchars((string)($d['name'] ?? '')) ?>
          <?php endif ?>
        </h5>
        <div class="text-muted small mb-2">Time: <?= htmlspecialchars((string)($d['time'] ?? '')) ?></div>
      </div>
      <div>
        <span class="badge bg-info text-dark text-uppercase">
          <?= htmlspecialchars((string)($d['state'] ?? '')) ?>
        </span>
      </div>
    </div>
    <?php if( trim((string)($d['result'] ?? '')) !== '' ) : ?>
      <div class="mb-2"><strong>Result:</strong> <?= nl2br(htmlspecialchars((string)$d['result'])) ?></div>
    <?php endif ?>
    <?php if( trim((string)($d['description'] ?? '')) !== '' ) : ?>
      <div class="card-text mb-2"><?= nl2br(htmlspecialchars((string)$d['description'])) ?></div>
    <?php endif ?>
    <div class="text-muted small">Files Nr: #<?= htmlspecialchars((string)($d['files_nr'] ?? '')) ?></div>
  </div>
</div>
