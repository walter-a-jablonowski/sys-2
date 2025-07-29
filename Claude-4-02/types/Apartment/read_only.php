<h5>
  <?php if( isset($currentInstance['url']) && ! empty($currentInstance['url']) ): ?>
    <a href="<?= htmlspecialchars($currentInstance['url']) ?>" target="_blank" class="text-decoration-none">
      <?= htmlspecialchars($currentInstance['name']) ?>
      <i class="bi bi-box-arrow-up-right ms-1"></i>
    </a>
  <?php else: ?>
    <?= htmlspecialchars($currentInstance['name']) ?>
  <?php endif; ?>
</h5>

<div class="row mb-2">
  <div class="col-sm-3"><strong>State:</strong></div>
  <div class="col-sm-9">
    <?php 
    $state = $currentInstance['state'] ?? 'new';
    $stateBadgeClass = match($state) {
      'new' => 'bg-secondary',
      'current' => 'bg-primary',
      'maybe' => 'bg-warning',
      'done' => 'bg-success',
      default => 'bg-secondary'
    };
    $stateLabel = match($state) {
      'new' => 'New',
      'current' => 'Current',
      'maybe' => 'Maybe',
      'done' => 'Done',
      default => ucfirst($state)
    };
    ?>
    <span class="badge <?= $stateBadgeClass ?>"><?= $stateLabel ?></span>
  </div>
</div>

<div class="row mb-2">
  <div class="col-sm-3"><strong>Files Nr:</strong></div>
  <div class="col-sm-9"><?= htmlspecialchars($currentInstance['files_nr'] ?? '') ?></div>
</div>

<?php if( isset($currentInstance['result']) && ! empty($currentInstance['result']) ): ?>
<div class="row mb-2">
  <div class="col-sm-3"><strong>Result:</strong></div>
  <div class="col-sm-9"><?= htmlspecialchars($currentInstance['result']) ?></div>
</div>
<?php endif; ?>

<div class="row mb-2">
  <div class="col-sm-3"><strong>Created:</strong></div>
  <div class="col-sm-9"><?= htmlspecialchars($currentInstance['time']) ?></div>
</div>

<?php if( isset($currentInstance['description']) && ! empty($currentInstance['description']) ): ?>
<div class="row">
  <div class="col-sm-3"><strong>Description:</strong></div>
  <div class="col-sm-9"><?= nl2br(htmlspecialchars($currentInstance['description'])) ?></div>
</div>
<?php endif; ?>
