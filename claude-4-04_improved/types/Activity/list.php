<?php
// Activity list renderer
$priorityIcon = str_repeat('🏷️', $entry['priority'] ?? 1);
$state = $entry['state'] ?? 'new';
$stateClass = 'state-' . $state;
$priorityClass = 'priority-' . ($entry['priority'] ?? 1);
?>

<div class="d-flex justify-content-between align-items-center <?= $stateClass ?> <?= $priorityClass ?>">
  <div>
    <span class="me-2"><?= $priorityIcon ?><?= $entry['priority'] ?? 1 ?></span>
    <span><?= htmlspecialchars($entry['name']) ?></span>
  </div>
  <div class="text-end">
    <span class="badge bg-secondary"><?= ucfirst($state) ?></span>
    <?php if( isset($entry['dueDate']) ): ?>
    <br><small class="text-muted"><?= htmlspecialchars($entry['dueDate']) ?></small>
    <?php endif; ?>
  </div>
</div>
