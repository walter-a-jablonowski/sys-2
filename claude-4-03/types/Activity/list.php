<?php
// Activity list cell renderer

$priority = $entry['priority'] ?? 3;
$state = $entry['state'] ?? 'new';
$name = $entry['name'] ?? 'Untitled';
?>

<div class="d-flex justify-content-between align-items-center">
  <div>
    <span class="badge bg-primary priority-badge me-2"><?= $priority ?></span>
    <span class="fw-medium"><?= htmlspecialchars($name) ?></span>
  </div>
  <span class="badge state-badge state-<?= $state ?>"><?= ucfirst($state) ?></span>
</div>
