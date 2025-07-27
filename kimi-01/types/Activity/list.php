<?php
// Default list cell renderer for Activity type
$priority = $entry['data']['priority'] ?? 'medium';
$state = $entry['data']['state'] ?? 'open';
$dueDate = $entry['data']['dueDate'] ?? '';

$priorityBadge = '';
switch ($priority)
{
  case 'high':
    $priorityBadge = '<span class="badge bg-danger">High</span>';
    break;
  case 'medium':
    $priorityBadge = '<span class="badge bg-warning">Medium</span>';
    break;
  case 'low':
    $priorityBadge = '<span class="badge bg-secondary">Low</span>';
    break;
}

$stateBadge = '';
switch ($state)
{
  case 'open':
    $stateBadge = '<span class="badge bg-primary">Open</span>';
    break;
  case 'in_progress':
    $stateBadge = '<span class="badge bg-info">In Progress</span>';
    break;
  case 'done':
    $stateBadge = '<span class="badge bg-success">Done</span>';
    break;
  case 'cancelled':
    $stateBadge = '<span class="badge bg-dark">Cancelled</span>';
    break;
}

?>
<div>
  <h6 class="mb-1"><?= htmlspecialchars($entry['name']) ?></h6>
  <div class="mb-1">
    <?= $priorityBadge ?>
    <?= $stateBadge ?>
  </div>
  <?php if ($dueDate): ?>
    <small class="text-muted d-block">
      <i class="bi bi-calendar"></i> Due: <?= htmlspecialchars($dueDate) ?>
    </small>
  <?php endif; ?>
  <small class="text-muted d-block"><?= htmlspecialchars($entry['type']) ?></small>
</div>
