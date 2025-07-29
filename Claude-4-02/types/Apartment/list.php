<div>
  <!-- First line -->
  <div class="d-flex justify-content-between align-items-center mb-1">
    <div>
      <?php if( isset($instanceData['url']) && ! empty($instanceData['url']) ): ?>
        <a href="<?= htmlspecialchars($instanceData['url']) ?>" target="_blank" class="text-decoration-none">
          <strong><?= htmlspecialchars($instanceData['name']) ?></strong>
          <i class="bi bi-box-arrow-up-right ms-1"></i>
        </a>
      <?php else: ?>
        <strong><?= htmlspecialchars($instanceData['name']) ?></strong>
      <?php endif; ?>
    </div>
    <div>
      <?php 
      $state = $instanceData['state'] ?? 'new';
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
  
  <!-- Second line -->
  <div class="d-flex justify-content-between align-items-center">
    <small class="text-muted">
      <?= htmlspecialchars(substr($instanceData['time'] ?? '', 0, 10)) ?>
    </small>
    <small class="text-muted">
      <?= htmlspecialchars($instanceData['files_nr'] ?? '') ?>
    </small>
  </div>
</div>
