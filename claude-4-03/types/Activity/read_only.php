<?php
// Activity read-only renderer using PHP's alternative syntax

$name = $entry['name'] ?? 'Untitled';
$time = $entry['time'] ?? '';
$description = $entry['description'] ?? '';
$priority = $entry['priority'] ?? 3;
$state = $entry['state'] ?? 'new';
$dueDate = $entry['dueDate'] ?? '';
?>

<div class="activity-readonly">
  <h5><?= htmlspecialchars($name) ?></h5>
  
  <div class="row mb-3">
    <div class="col-6">
      <small class="text-muted">Priority:</small><br>
      <span class="badge bg-primary"><?= $priority ?></span>
    </div>
    <div class="col-6">
      <small class="text-muted">State:</small><br>
      <span class="badge state-badge state-<?= $state ?>"><?= ucfirst($state) ?></span>
    </div>
  </div>
  
  <?php if( $dueDate ): ?>
  <div class="mb-3">
    <small class="text-muted">Due Date:</small><br>
    <span><?= htmlspecialchars($dueDate) ?></span>
  </div>
  <?php endif; ?>
  
  <div class="mb-3">
    <small class="text-muted">Created:</small><br>
    <span><?= htmlspecialchars($time) ?></span>
  </div>
  
  <?php if( $description ): ?>
  <div class="mb-3">
    <small class="text-muted">Description:</small><br>
    <p><?= nl2br(htmlspecialchars($description)) ?></p>
  </div>
  <?php endif; ?>
</div>
