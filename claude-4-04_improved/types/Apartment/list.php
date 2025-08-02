<?php
// Apartment list renderer
$state = $entry['state'] ?? 'new';
$stateClass = 'state-' . $state;
$date = isset($entry['time']) ? date('Y-m-d', strtotime($entry['time'])) : '';
$files_nr = $entry['files_nr'] ?? '0000';
$hasUrl = !empty($entry['url']);
?>

<div class="d-flex justify-content-between align-items-center type-apartment <?= $stateClass ?>">
  <div>
    <span><?= htmlspecialchars($entry['name']) ?></span>
    <?php if( $hasUrl ): ?>
    <span class="ms-2">🔗</span>
    <?php endif; ?>
    <br>
    <small class="text-muted"><?= htmlspecialchars($date) ?></small>
  </div>
  <div class="text-end">
    <span class="badge bg-secondary"><?= ucfirst($state) ?></span>
    <br>
    <small class="text-muted"><?= htmlspecialchars($files_nr) ?></small>
  </div>
</div>
