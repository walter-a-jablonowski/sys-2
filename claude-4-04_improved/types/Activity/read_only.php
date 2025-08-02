<?php
// Activity read-only renderer
$priority = $entry['priority'] ?? 1;
$state = $entry['state'] ?? 'new';
$stateClass = 'state-' . $state;
$priorityClass = 'priority-' . $priority;
?>

<div class="<?= $stateClass ?> <?= $priorityClass ?>">
  <div class="d-flex justify-content-between align-items-start mb-2">
    <h5 class="mb-0">
      <span class="me-2">🏷️<?= $priority ?></span>
      <?= htmlspecialchars($entry['name']) ?>
    </h5>
    <span class="badge bg-secondary"><?= ucfirst($state) ?></span>
  </div>
  
  <?php if( isset($entry['dueDate']) ): ?>
  <p class="mb-2">
    <strong>Due Date:</strong> <?= htmlspecialchars($entry['dueDate']) ?>
  </p>
  <?php endif; ?>
  
  <p class="mb-2">
    <strong>Created:</strong> <?= htmlspecialchars($entry['time']) ?>
  </p>
  
  <?php if( isset($entry['description']) && $entry['description'] ): ?>
  <div class="mt-3">
    <strong>Description:</strong>
    <div class="mt-2">
      <?= nl2br(htmlspecialchars($entry['description'])) ?>
    </div>
  </div>
  <?php endif; ?>
</div>
