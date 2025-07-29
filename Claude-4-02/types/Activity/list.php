<div class="d-flex justify-content-between align-items-center">
  <div>
    <?php 
    $priority = $instanceData['priority'] ?? 1;
    $badgeClass = match($priority) {
      1 => 'bg-success',
      2 => 'bg-info', 
      3 => 'bg-warning',
      4 => 'bg-danger',
      5 => 'bg-dark',
      default => 'bg-secondary'
    };
    ?>
    <span class="badge <?= $badgeClass ?> me-2"><?= $priority ?></span>
    <strong><?= htmlspecialchars($instanceData['name']) ?></strong>
  </div>
  <div>
    <?php 
    $state = $instanceData['state'] ?? 'new';
    $stateBadgeClass = match($state) {
      'new' => 'bg-secondary',
      'progress' => 'bg-primary',
      'done' => 'bg-success',
      default => 'bg-secondary'
    };
    $stateLabel = match($state) {
      'new' => 'New',
      'progress' => 'In Progress', 
      'done' => 'Done',
      default => ucfirst($state)
    };
    ?>
    <span class="badge <?= $stateBadgeClass ?>"><?= $stateLabel ?></span>
  </div>
</div>
